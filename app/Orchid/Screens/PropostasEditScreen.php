<?php

namespace App\Orchid\Screens;

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

class PropostasEditScreen extends Screen
{
    public $proposta;
    public $leadName = 'Novo Cliente';
    public $name;
    public $description;

    /**
     * Controle de estado para saber se é apenas visualização.
     */
    public $isViewMode = false;

    /**
     * Carrega os dados da proposta e lead para a tela.
     */
    public function query(Proposta $proposta, Lead $lead = null): array
    {
        // Detecta se estamos na rota de visualização
        $this->isViewMode = request()->route()->getName() === 'platform.propostas.view';

        if (!$proposta->exists && $lead) {
            $proposta->lead_id = $lead->id;
        }

        $this->proposta = $proposta;
        
        $this->leadName = $proposta->lead?->nome
            ?? $lead?->nome
            ?? ($proposta->lead_id ? "Lead #{$proposta->lead_id}" : 'Novo Cliente');

        $this->name = $proposta->exists ? "Editar Proposta #{$proposta->id}" : 'Criar Nova Proposta';

        // Ajusta títulos e descrição baseado no modo
        if ($this->isViewMode) {
            $this->name = "Visualizar Proposta #{$proposta->id}";
            $this->description = "Detalhes da simulação para: {$this->leadName}";
        } else {
            $this->description = "Simulação para: {$this->leadName}";
        }

        return [
            'proposta'   => $proposta,
            'isViewMode' => $this->isViewMode,
        ];
    }

    /**
     * Retorna o título da tela.
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * Retorna a descrição da tela.
     */
    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * Define a barra de comandos da tela.
     */
    public function commandBar(): array
    {
        $actions = [];

        // Botão Salvar (Apenas se NÃO for visualização)
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

            // Botão Excluir (Apenas se NÃO for visualização)
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
     * Define o layout da tela.
     */
    public function layout(): array
    {
        // MODO VISUALIZAÇÃO: Carrega o blade personalizado de leitura
        if ($this->isViewMode) {
            return [
                Layout::view('platform.propostas.view', [
                    'proposta' => $this->proposta,
                ]),
            ];
        }

        // MODO EDIÇÃO: Carrega o formulário e o simulador interativo
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
     * Cria ou atualiza uma proposta.
     */
    public function createOrUpdate(Proposta $proposta, Request $request, EntradaCalculatorService $calc)
    {
        if ($this->isViewMode) {
            return redirect()->back();
        }

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
        
        // Recalcula valores antes de salvar para garantir integridade
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

    /**
     * Remove uma proposta.
     */
    public function remove(Proposta $proposta)
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

    /**
     * Endpoint AJAX para simulação dinâmica (Turbo / Fetch).
     */
    public function ajaxCalculate(Request $request, EntradaCalculatorService $calc)
    {
        $dados = $calc->calcular($request->input('proposta', []));
        return response()->json($dados);
    }

    /**
     * Gera o PDF da proposta.
     */
    public function generatePdf(Proposta $proposta)
    {
        $data = [
            'proposta' => $proposta,
        ];
        $pdf = \PDF::loadView('platform.propostas.pdf', $data); 
        return $pdf->stream("proposta_{$proposta->id}.pdf");
    }

    /**
     * MÉTODO DE BLINDAGEM (Armor)
     * * Captura chamadas de métodos inexistentes.
     * Se o Laravel tentar chamar o ID da proposta (ex: "28") como método devido a um erro de rota,
     * este método intercepta e evita o erro fatal (ReflectionException/BadMethodCallException).
     */
    public function __call($name, $arguments)
    {
        // Se o "nome do método" for um número, é um falso positivo de rota. Ignoramos.
        if (is_numeric($name)) {
            return null;
        }

        // Se for erro real de código, lança o erro normal do PHP
        trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
    }
}