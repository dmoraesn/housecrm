<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Propostas;

use App\Models\Lead;
use App\Models\Proposta;
use App\Services\EntradaCalculatorService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

/**
 * Tela de edição e visualização de propostas no painel Orchid.
 */
class PropostasEditScreen extends Screen
{
    private Proposta $proposta;
    private bool $isViewMode;
    private string $leadName;
    private ?string $name;
    private ?string $description;

    /**
     * Prepara os dados para a tela.
     */
    public function query(Proposta $proposta, Lead $lead = null): array
    {
        $this->isViewMode = request()->route()->getName() === 'platform.propostas.view';

        if (!$proposta->exists && $lead) {
            $proposta->lead_id = $lead->id;
        }

        $this->proposta = $proposta->load(['lead', 'construtora']); // Carregamento centralizado de relacionamentos

        $this->leadName = $this->resolveLeadName($proposta, $lead);
        $this->name = $this->resolveScreenName($proposta);
        $this->description = $this->resolveScreenDescription();

        return [
            'proposta' => $proposta,
            'isViewMode' => $this->isViewMode,
        ];
    }

    /**
     * Resolve o nome do lead para exibição.
     */
    private function resolveLeadName(Proposta $proposta, ?Lead $lead): string
    {
        return $proposta->lead?->nome
            ?? $lead?->nome
            ?? ($proposta->lead_id ? "Lead #{$proposta->lead_id}" : 'Novo Cliente');
    }

    /**
     * Resolve o título da tela.
     */
    private function resolveScreenName(Proposta $proposta): string
    {
        if ($this->isViewMode) {
            return "Visualizar Proposta #{$proposta->id}";
        }

        return $proposta->exists ? "Editar Proposta #{$proposta->id}" : 'Criar Nova Proposta';
    }

    /**
     * Resolve a descrição da tela.
     */
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

    /**
     * Constrói as ações da barra de comandos.
     */
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

    /**
     * Constrói o layout da tela.
     */
    public function layout(): array
    {
        if ($this->isViewMode) {
            return $this->buildLayoutForViewMode();
        }

        return $this->buildLayoutForEditMode();
    }

    /**
     * Layout para modo de visualização.
     */
    private function buildLayoutForViewMode(): array
    {
        return [
            Layout::view('platform.propostas.view', [
                'proposta' => $this->proposta,
            ]),
        ];
    }

    /**
     * Layout para modo de edição.
     */
    private function buildLayoutForEditMode(): array
    {
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

    /**
     * Cria ou atualiza a proposta.
     */
    public function createOrUpdate(Proposta $proposta, Request $request, EntradaCalculatorService $calc): \Illuminate\Http\RedirectResponse
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
            \Log::info('Proposta salva: ID ' . $proposta->id, ['data' => $data]); // Log para auditoria
        } catch (\Throwable $e) {
            \Log::error('Erro ao salvar proposta: ' . $e->getMessage(), ['data' => $data]);
            Toast::error('Erro ao salvar: ' . $e->getMessage());

            return back()->withInput();
        }

        return redirect()->route('platform.propostas.edit', $proposta);
    }

    /**
     * Remove a proposta.
     */
    public function remove(Proposta $proposta): \Illuminate\Http\RedirectResponse
    {
        if ($this->isViewMode) {
            return redirect()->back();
        }

        try {
            $proposta->delete();
            Toast::info('Proposta removida com sucesso.');
            \Log::info('Proposta removida: ID ' . $proposta->id);
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir proposta: ' . $e->getMessage(), ['id' => $proposta->id]);
            Toast::error('Erro ao excluir: ' . $e->getMessage());
        }

        return redirect()->route('platform.propostas');
    }

    /**
     * Calcula via AJAX.
     */
    public function ajaxCalculate(Request $request, EntradaCalculatorService $calc): \Illuminate\Http\JsonResponse
    {
        $dados = $calc->calcular($request->input('proposta', []));

        return response()->json($dados);
    }

    /**
     * Gera o PDF da proposta.
     */
/**
 * Gera o PDF da proposta.
 *
 * @param Proposta $proposta
 * @return \Illuminate\Http\Response
 */
public function generatePdf(Proposta $proposta): \Illuminate\Http\Response
{
    if (!$proposta->exists) {
        abort(404, 'Proposta não encontrada.');
    }

    $proposta->loadMissing(['lead', 'construtora']); // Garante carregamento se não feito
    $empresa = \App\Models\ImobiliariaConfig::first();

    $data = [
        'proposta' => $proposta,
        'empresa' => $empresa,
        'logo' => $this->handleLogoPath($empresa),
    ];

    $pdf = \PDF::loadView('platform.propostas.pdf', $data);
    $pdf->setOptions(['isRemoteEnabled' => true]);

    return $pdf->stream("proposta_{$proposta->id}.pdf");
}
    /**
     * Resolve o caminho do logo para PDF.
     */
    private function handleLogoPath($empresa): ?string
    {
        if (!$empresa || !$empresa->logo) {
            return null;
        }

        // Prioriza caminho absoluto para melhor compatibilidade com DomPDF
        $relativePath = $empresa->logo->path . $empresa->logo->name . '.' . $empresa->logo->extension;
        $absolutePath = public_path('storage/' . $relativePath);

        return file_exists($absolutePath) ? $absolutePath : $empresa->logo->url();
    }
}