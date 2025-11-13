<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Fluxo;
use App\Models\Lead;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\RadioButtons;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

/**
 * Classe responsável pela tela de fluxo financeiro (Pro Soluto)
 * com cálculo automático de financiamento, parcelas e entrada.
 * 
 * Agora com toggle para selecionar modo de cálculo:
 * - Percentual: calcula o valor financiado a partir do percentual informado.
 * - Manual: calcula o percentual a partir do valor financiado informado.
 */
class FluxoScreen extends Screen
{
    /**
     * Nome da tela.
     *
     * @var string
     */
    public $name = 'Plano de Pagamento de Entrada';

    /**
     * Descrição da tela.
     *
     * @var string
     */
    public $description = 'Calculadora financeira para fluxo de entrada (Pro Soluto) com sugestão automática de financiamento e controle de cálculo via toggle.';

    /**
     * Query inicial com dados.
     */
    public function query(Fluxo $fluxo): array
    {
        return [
            'fluxo' => $fluxo,
            'lead' => $fluxo->lead ?? null,
        ];
    }

    /**
     * Barra de ações da tela.
     */
    public function commandBar(): array
    {
        return [
            Button::make('Adicionar Lead')
                ->icon('bs.person-plus')
                ->method('addLead'),

            Button::make('Salvar Rascunho')
                ->icon('bs.pen')
                ->novalidate()
                ->method('saveFluxo')
                ->parameters(['status' => 'draft']),

            Button::make('Salvar Fluxo')
                ->icon('bs.check')
                ->method('saveFluxo')
                ->parameters(['status' => 'completed']),

            Button::make('Novo Fluxo')
                ->icon('bs.arrow-clockwise')
                ->novalidate()
                ->method('resetFluxo'),
        ];
    }

    /**
     * Layout principal da tela de fluxo.
     */
    public function layout(): array
    {
        $readonlyStyle = 'readonly-highlight bg-light border-primary fw-semibold text-primary';

        return [

            // ===============================================================
            // 1. LEAD ASSOCIADO
            // ===============================================================
            Layout::rows([
                Relation::make('fluxo.lead_id')
                    ->title('Lead Associado')
                    ->fromModel(Lead::class, 'nome', 'id')
                    ->placeholder('Selecione um lead existente para vincular a este fluxo.')
                    ->help('O lead vinculado será associado ao cálculo financeiro deste fluxo.'),
            ]),

            // ===============================================================
            // 2. RESUMO DO IMÓVEL E FINANCIAMENTO
            // ===============================================================
            Layout::rows([
                Group::make([

                    Input::make('fluxo.valor_imovel')
                        ->title('Valor do Imóvel')
                        ->id('valor_imovel')
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ])
                        ->help('Valor total do imóvel conforme contrato.'),

                    Input::make('fluxo.valor_avaliacao')
                        ->title('Valor de Avaliação')
                        ->id('valor_avaliacao')
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ])
                        ->help('Avaliação feita pela instituição financeira.'),
                ]),

                RadioButtons::make('fluxo.modo_calculo')
                    ->title('Modo de Cálculo do Financiamento')
                    ->options([
                        'percentual' => 'Calcular pelo Percentual',
                        'manual' => 'Definir Valor Manualmente',
                    ])
                    ->value('percentual')
                    ->id('modo_calculo')
                    ->help('Escolha como deseja calcular o valor do financiamento.'),

                Group::make([
                    Input::make('fluxo.financiamento_percentual')
                        ->title('Financiamento (%)')
                        ->id('financiamento_percentual')
                        ->type('number')
                        ->min(10)
                        ->max(100)
                        ->step(1)
                        ->value(80)
                        ->help('Percentual de financiamento baseado no valor de avaliação.'),

                    Input::make('fluxo.valor_financiado')
                        ->title('Valor Financiado')
                        ->id('valor_financiado')
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ])
                        ->help('Valor efetivo financiado. Calculado automaticamente ou editável conforme o modo.'),
                ])->fullWidth(),

                Input::make('fluxo.valor_bonus_descontos')
                    ->title('Bônus / Descontos / FGTS')
                    ->id('valor_bonus_descontos')
                    ->mask([
                        'alias' => 'currency',
                        'prefix' => 'R$ ',
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'digits' => 2,
                    ])
                    ->help('Valores de FGTS, descontos e bônus aplicáveis.'),
            ])->title('1. Resumo do Imóvel e Financiamento'),

            // ===============================================================
            // 3. ENTRADA MÍNIMA
            // ===============================================================
            Layout::rows([
                Input::make('fluxo.entrada_minima')
                    ->title('Entrada Mínima Necessária')
                    ->id('entrada_minima')
                    ->readonly()
                    ->class($readonlyStyle . ' text-danger')
                    ->mask([
                        'alias' => 'currency',
                        'prefix' => 'R$ ',
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'digits' => 2,
                    ])
                    ->help('Calculada automaticamente: Imóvel - Financiamento - Descontos.'),
            ]),

            // ===============================================================
            // 4. VALORES ALTOS E BALÕES
            // ===============================================================
            Layout::columns([
                Layout::rows([
                    Input::make('fluxo.valor_assinatura_contrato')
                        ->title('Assinatura do Contrato')
                        ->id('valor_assinatura_contrato')
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ])
                        ->help('Valor pago na assinatura do contrato.'),

                    Input::make('fluxo.valor_na_chaves')
                        ->title('Entrega das Chaves')
                        ->id('valor_na_chaves')
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ])
                        ->help('Valor pago na entrega das chaves.'),
                ])->title('2. Valores Altos (Assinatura e Chaves)'),

                Layout::rows([
                    Matrix::make('fluxo.baloes')
                        ->title('Balões de Pagamento')
                        ->columns(['Data' => 'data', 'Valor' => 'valor'])
                        ->fields([
                            'data' => Input::make()->type('date'),
                            'valor' => Input::make()->mask([
                                'alias' => 'currency',
                                'prefix' => 'R$ ',
                                'groupSeparator' => '.',
                                'radixPoint' => ',',
                                'digits' => 2,
                            ]),
                        ])
                        ->help('Adicione balões de pagamento personalizados.'),
                ])->title('3. Balões de Pagamento'),
            ]),

            // ===============================================================
            // 5. PARCELAMENTO
            // ===============================================================
            Layout::columns([
                Layout::rows([
                    Group::make([
                        Input::make('fluxo.parcelas_qtd')
                            ->title('Número de Parcelas')
                            ->id('parcelas_qtd')
                            ->type('number')
                            ->help('Quantidade de parcelas da entrada.'),

                        Input::make('fluxo.valor_parcela')
                            ->title('Valor por Parcela')
                            ->id('valor_parcela')
                            ->readonly()
                            ->class($readonlyStyle)
                            ->mask([
                                'alias' => 'currency',
                                'prefix' => 'R$ ',
                                'groupSeparator' => '.',
                                'radixPoint' => ',',
                                'digits' => 2,
                            ]),
                    ]),

                    Input::make('fluxo.total_parcelamento')
                        ->title('Total do Parcelamento')
                        ->id('total_parcelamento')
                        ->readonly()
                        ->class($readonlyStyle)
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ]),
                ])->title('4. Parcelamento Mensal'),

                Layout::rows([
                    Input::make('fluxo.valor_total_entrada')
                        ->title('Valor Total da Entrada')
                        ->id('valor_total_entrada')
                        ->readonly()
                        ->class($readonlyStyle)
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ]),

                    Input::make('fluxo.valor_restante')
                        ->title('Valor Restante (Geral)')
                        ->id('valor_restante')
                        ->readonly()
                        ->class($readonlyStyle)
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ]),
                ])->title('Resumo da Entrada'),
            ]),

            Layout::rows([
                TextArea::make('fluxo.observacao')
                    ->title('5. Observações')
                    ->rows(5)
                    ->help('Notas adicionais sobre este fluxo.'),
            ]),

            Layout::view('orchid.fluxo-topo'),
            Layout::view('orchid.fluxo-calculo-js'),
        ];
    }

    public function saveFluxo(Request $request)
    {
        $dados = $request->get('fluxo');
        $dados['status'] = $request->get('status', 'completed');
        Fluxo::updateOrCreate(['id' => $dados['id'] ?? null], $dados);
        Toast::info('Fluxo salvo com sucesso!');
    }

    public function addLead(Request $request)
    {
        $leadId = $request->input('fluxo.lead_id');
        $fluxoId = $request->input('fluxo.id');

        if (!$leadId) {
            Toast::warning('Selecione um lead para vincular.');
            return;
        }

        $fluxo = Fluxo::find($fluxoId);
        if ($fluxo) {
            $fluxo->lead_id = $leadId;
            $fluxo->save();
            Toast::info('Lead vinculado com sucesso!');
        }
    }

    public function resetFluxo()
    {
        Toast::info('Fluxo reiniciado.');
        return redirect()->route('platform.fluxo.create');
    }
}
