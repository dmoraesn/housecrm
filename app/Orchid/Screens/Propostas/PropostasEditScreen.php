<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Propostas;

use App\Models\Lead;
use App\Models\Proposta;
use App\Services\EntradaCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Redirect; // Importação necessária

class PropostasEditScreen extends Screen
{
    private Proposta $proposta;
    private bool $isViewMode;
    private string $leadName;
    private ?string $name;
    private ?string $description;

    public function query(Proposta $proposta, Lead $lead = null): array|RedirectResponse
    {
        // CORREÇÃO APLICADA: Redirecionar se estiver no modo de criação (sem proposta e sem lead)
        if (!$proposta->exists && is_null($lead)) {
            Toast::info('A criação de novas propostas foi movida para o Fluxo de Leads.');
            return Redirect::to(url('/admin/fluxo'));
        }

        $this->isViewMode = request()->route()->getName() === 'platform.propostas.view';

        if (!$proposta->exists && $lead) {
            $proposta->lead_id = $lead->id;
        }

        $this->proposta = $proposta->load(['lead', 'construtora']);

        $this->leadName = $this->resolveLeadName($proposta, $lead);
        $this->name = $this->resolveScreenName($proposta);
        $this->description = $this->resolveScreenDescription();

        return [
            'proposta' => $proposta,
            'isViewMode' => $this->isViewMode,
        ];
    }

    private function resolveLeadName(Proposta $proposta, ?Lead $lead): string
    {
        return $proposta->lead?->nome
            ?? $lead?->nome
            ?? ($proposta->lead_id ? "Lead #{$proposta->lead_id}" : 'Novo Cliente');
    }

    private function resolveScreenName(Proposta $proposta): string
    {
        if ($this->isViewMode) {
            return "Visualizar Proposta #{$proposta->id}";
        }

        return $proposta->exists ? "Editar Proposta #{$proposta->id}" : 'Criar Nova Proposta';
    }

    private function resolveScreenDescription(): string
    {
        $prefix = $this->isViewMode ? 'Detalhes da simulação para' : 'Simulação para';
        return "{$prefix}: {$this->leadName}";
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function commandBar(): array
    {
        $actions = [];

        if (!$this->isViewMode) {
            $actions[] = Button::make($this->proposta->exists ? 'Atualizar' : 'Salvar Proposta')
                ->icon($this->proposta->exists ? 'bs.pencil' : 'bs.check-circle')
                ->method('createOrUpdate');
        }

        if ($this->proposta->exists) {
            $actions[] = Link::make('Gerar PDF')
                ->icon('bs.file-pdf')
                ->route('platform.propostas.pdf', $this->proposta)
                ->target('_blank');

            if (!$this->isViewMode) {
                $actions[] = Button::make('Excluir')
                    ->icon('bs.trash')
                    ->method('remove')
                    ->confirm('Tem certeza? Esta ação é irreversível.');
            }
        }

        return $actions;
    }

    public function layout(): array
    {
        if ($this->isViewMode) {
            return [
                Layout::view('platform.propostas.view', [
                    'proposta' => $this->proposta,
                ]),
            ];
        }

        return [
            Layout::rows([
                Relation::make('proposta.lead_id')
                    ->title('Cliente Relacionado *')
                    ->fromModel(Lead::class, 'nome')
                    ->required()
                    ->disabled($this->isViewMode)
                    ->help('Selecione o lead que será convertido em cliente ao salvar.')
            ])->title('Lead Vinculado'),

            Layout::view('platform.propostas.simulador', [
                'proposta' => $this->proposta,
            ]),
        ];
    }

    public function createOrUpdate(Proposta $proposta, Request $request, EntradaCalculatorService $calc): RedirectResponse
    {
        if ($this->isViewMode) {
            return redirect()->back();
        }

        $data = $request->input('proposta', []);

        if (empty($data['lead_id'])) {
            Toast::error('Selecione um cliente (lead).');
            return back()->withInput();
        }

        $lead = Lead::find($data['lead_id']);

        if (!$lead) {
            Toast::error('Lead não encontrado.');
            return back()->withInput();
        }

        $data['cliente'] = $lead->nome ?? 'Cliente Desconhecido';

        $calculado = $calc->calcular($data);
        $data = array_merge($data, $calculado);

        try {
            $proposta->fill($data)->save();
            Toast::success('Proposta salva com sucesso!');
        } catch (\Throwable $e) {
            Toast::error('Erro ao salvar: ' . $e->getMessage());
            return back()->withInput();
        }

        return redirect()->route('platform.propostas.edit', $proposta);
    }

    public function remove(Proposta $proposta): RedirectResponse
    {
        if ($this->isViewMode) {
            return redirect()->back();
        }

        try {
            $proposta->delete();
            Toast::info('Proposta removida com sucesso.');
        } catch (\Exception $e) {
            Toast::error('Erro ao excluir: ' . $e->getMessage());
        }

        return redirect()->route('platform.propostas');
    }

    public function ajaxCalculate(Request $request, EntradaCalculatorService $calc): JsonResponse
    {
        $dados = $calc->calcular($request->input('proposta', []));
        return response()->json($dados);
    }

    /**
     * NOVO GERADOR DE PDF — BROWSER SHOT
     */
    public function generatePdf(Proposta $proposta)
    {
        if (!$proposta->exists) {
            abort(404, 'Proposta não encontrada.');
        }

        $proposta->fresh()->load(['lead', 'construtora', 'fluxo']);

        $empresa = \App\Models\ImobiliariaConfig::first();

        $html = view('platform.propostas.pdf', [
            'proposta' => $proposta,
            'empresa'  => $empresa,
            'logo'     => $this->handleLogoPath($empresa),
        ])->render();

        $outputPath = storage_path("app/pdfs/proposta_{$proposta->id}.pdf");

        Browsershot::html($html)
            ->format('A4')
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->margins(10, 10, 10, 10)
            ->save($outputPath);

        return response()->file($outputPath);
    }

    private function handleLogoPath($empresa): ?string
    {
        if (!$empresa || !$empresa->logo) {
            return null;
        }

        $relativePath = $empresa->logo->path . $empresa->logo->name . '.' . $empresa->logo->extension;
        $absolutePath = public_path('storage/' . $relativePath);

        return file_exists($absolutePath) ? $absolutePath : $empresa->logo->url();
    }
}