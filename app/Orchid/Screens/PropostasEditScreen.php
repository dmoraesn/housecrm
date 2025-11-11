<?php

namespace App\Orchid\Screens;

use App\Models\Proposta;
use App\Models\Lead;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Relation;
use Orchid\Support\Facades\Toast;
use App\Services\EntradaCalculatorService;

class PropostasEditScreen extends Screen
{
    public $proposta;
    public $leadName = 'Novo Cliente';
    public $name;
    public $description;

    public function query(Proposta $proposta, Lead $lead = null): array
    {
        if (!$proposta->exists && $lead) {
            $proposta->lead_id = $lead->id;
        }

        $this->proposta = $proposta;
        $this->leadName = $proposta->lead?->nome
            ?? $lead?->nome
            ?? ($proposta->lead_id ? "Lead #{$proposta->lead_id}" : 'Novo Cliente');

        $this->name = $proposta->exists ? "Editar Proposta #{$proposta->id}" : 'Criar Nova Proposta';
        $this->description = "Simulação para: {$this->leadName}";

        return ['proposta' => $proposta];
    }

    public function name(): ?string { return $this->name; }
    public function description(): ?string { return $this->description; }

    public function commandBar(): array
    {
        $actions = [
            Button::make($this->proposta->exists ? 'Atualizar' : 'Salvar Proposta')
                ->icon($this->proposta->exists ? 'bs.pencil' : 'bs.check-circle')
                ->method('createOrUpdate'),
        ];

        if ($this->proposta->exists) {
            $actions[] = Link::make('Gerar PDF')
                ->icon('bs.file-pdf')
                ->route('platform.propostas.pdf', $this->proposta)
                ->target('_blank');

            $actions[] = Button::make('Excluir')
                ->icon('bs.trash')
                ->method('remove')
                ->confirm('Tem certeza? Esta ação é irreversível.');
        }

        return $actions;
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                Relation::make('proposta.lead_id')
                    ->title('Cliente Relacionado *')
                    ->fromModel(Lead::class, 'nome')
                    ->required()
                    ->help('Selecione o lead que será convertido em cliente ao salvar.')
            ])->title('Lead Vinculado'),

            Layout::view('platform.propostas.simulador', [
                'proposta' => $this->proposta,
            ]),
        ];
    }

    public function createOrUpdate(Proposta $proposta, Request $request, EntradaCalculatorService $calc)
    {
        $data = $request->input('proposta', []);

        // Valida lead
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

        // Cálculo backend
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

    public function remove(Proposta $proposta)
    {
        try {
            $proposta->delete();
            Toast::info('Proposta removida com sucesso.');
        } catch (\Exception $e) {
            Toast::error('Erro ao excluir: ' . $e->getMessage());
        }

        return redirect()->route('platform.propostas');
    }

    /**
     * Endpoint AJAX opcional para simulação dinâmica (Turbo / Fetch).
     */
    public function ajaxCalculate(Request $request, EntradaCalculatorService $calc)
    {
        $dados = $calc->calcular($request->input('proposta', []));
        return response()->json($dados);
    }
}
