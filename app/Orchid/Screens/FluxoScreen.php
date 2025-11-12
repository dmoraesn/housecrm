<?php
declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Fluxo;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class FluxoScreen extends Screen
{
    /**
     * Título principal da tela.
     */
    public $name = 'Plano de Pagamento de Entrada';

    /**
     * Descrição auxiliar.
     */
    public $description = 'Calculadora financeira para fluxo de entrada (Pro Soluto) com sugestão automática de financiamento.';

    /**
     * Carrega o fluxo existente ou inicializa um novo.
     */
    public function query(Fluxo $fluxo): array
    {
        return [
            'fluxo' => $fluxo,
            'lead'  => $fluxo->lead ?? null,
        ];
    }

    /**
     * Barra de comandos superiores (ações).
     */
    public function commandBar(): array
    {
        return [
            Button::make('Adicionar Lead')
                ->icon('bs.person-plus')
                ->novalidate()
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
        ];
    }

    /**
     * Estrutura de layout da tela.
     */
    public function layout(): array
    {
        $highlightReadOnly = 'readonly-highlight bg-light border-primary fw-semibold text-primary';

        return [
            /**
             * 1️⃣ Resumo do Imóvel e Financiamento
             */
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
                        ]),

                    Input::make('fluxo.valor_avaliacao')
                        ->title('Valor de Avaliação')
                        ->id('valor_avaliacao')
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ]),

                    Input::make('fluxo.valor_financiamento_sugerido')
                        ->title('Financiamento Máximo (80%)')
                        ->id('valor_financiamento_sugerido')
                        ->class($highlightReadOnly)
                        ->readonly()
                        ->help('Calculado automaticamente com base na avaliação.'),

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
                        ->help('Valor efetivo financiado (editável).'),

                    Input::make('fluxo.valor_bonus_descontos')
                        ->title('Bônus / Descontos / FGTS')
                        ->id('valor_bonus_descontos')
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ]),
                ])->fullWidth(),
            ])->title('1. Resumo do Imóvel e Financiamento'),

            /**
             * 2️⃣ Entrada mínima necessária
             */
            Layout::rows([
                Input::make('fluxo.entrada_minima')
                    ->title('Entrada Mínima Necessária')
                    ->id('entrada_minima')
                    ->class($highlightReadOnly . ' text-danger')
                    ->mask([
                        'alias' => 'currency',
                        'prefix' => 'R$ ',
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'digits' => 2,
                    ])
                    ->readonly()
                    ->help('Calculada automaticamente: Imóvel - Financiamento - Descontos.'),
            ]),

            /**
             * 3️⃣ Assinatura, Chaves e Balões
             */
            Layout::columns([
                // Valores altos fixos
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
                        ]),

                    Input::make('fluxo.valor_na_chaves')
                        ->title('Entrega das Chaves')
                        ->id('valor_na_chaves')
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ]),
                ])->title('2. Valores Altos (Assinatura e Chaves)'),

                // Balões de pagamento
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
                        ]),
                ])->title('3. Balões de Pagamento'),
            ]),

            /**
             * 4️⃣ Parcelamento e totais
             */
            Layout::columns([
                Layout::rows([
                    Group::make([
                        Input::make('fluxo.parcelas_qtd')
                            ->title('Número de Parcelas')
                            ->id('parcelas_qtd')
                            ->type('number'),

                        Input::make('fluxo.valor_parcela')
                            ->title('Valor por Parcela')
                            ->id('valor_parcela')
                            ->class($highlightReadOnly)
                            ->readonly()
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
                        ->class($highlightReadOnly)
                        ->readonly()
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
                        ->class($highlightReadOnly)
                        ->readonly()
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
                        ->class($highlightReadOnly)
                        ->readonly()
                        ->mask([
                            'alias' => 'currency',
                            'prefix' => 'R$ ',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'digits' => 2,
                        ]),
                ])->title('Resumo da Entrada'),
            ]),

            /**
             * 5️⃣ Observações
             */
            Layout::rows([
                TextArea::make('fluxo.observacao')
                    ->title('5. Observações')
                    ->rows(5),
            ]),

            /**
             * Inclui os visuais JS e o topo flutuante
             */
            Layout::view('orchid.fluxo-topo'),
            Layout::view('orchid.fluxo-calculo-js'),
        ];
    }

    /**
     * Método de salvamento do fluxo (rascunho ou final).
     */
    public function saveFluxo(Request $request)
    {
        $status = $request->get('status', 'completed');
        $data = $request->get('fluxo');
        $data['status'] = $status;

        Fluxo::updateOrCreate(['id' => $data['id'] ?? null], $data);

        if ($status === 'draft') {
            Toast::info('Rascunho do fluxo salvo com sucesso!');
        } else {
            Toast::info('Fluxo finalizado e salvo com sucesso!');
        }
    }

    /**
     * Exemplo de método de Lead (simulado).
     */
    public function addLead(Request $request)
    {
        Toast::info('Funcionalidade de Adicionar Lead acionada.');
    }
}
