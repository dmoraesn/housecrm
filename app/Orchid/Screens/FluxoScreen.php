<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Fluxo;
use App\Models\Lead;
use App\Models\Proposta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
 *
 * Botão PDF removido: Fluxo agora é convertido em Proposta ao salvar.
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
     * Query inicial com dados, formatando valores monetários para exibição.
     */
    public function query(Fluxo $fluxo): array
    {
        // Para edições, usa acessors do modelo para formatar em BRL
        if ($fluxo->exists) {
            $fluxo->load('lead');
            $fluxo->append([
                'valor_imovel_formatted',
                'valor_avaliacao_formatted',
                'entrada_minima_formatted',
                // Adicione mais acessors conforme necessário
            ]);
        }
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
            Button::make('Salvar Rascunho')
                ->icon('bs.pen')
                ->novalidate()
                ->method('saveFluxo')
                ->parameters(['status' => Fluxo::STATUS_DRAFT]),

            Button::make('Salvar como Proposta')
                ->icon('bs.check')
                ->method('saveFluxo')
                ->parameters(['status' => Fluxo::STATUS_COMPLETED]),

            Button::make('Limpar Fluxo')
                ->icon('bs.arrow-counterclockwise')
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
                    ->applyScope('ativos')
                    ->placeholder('Selecione um lead existente para vincular a este fluxo.')
                    ->help('O lead vinculado será associado ao cálculo financeiro deste fluxo. Isso permite integração futura com propostas.'),
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
                        ->help('Valor total do imóvel conforme contrato. Deve ser maior que a avaliação em casos específicos.'),

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
                        ->help('Avaliação feita pela instituição financeira. Impacta o cálculo do financiamento.'),
                ]),

                RadioButtons::make('fluxo.base_calculo')
                    ->title('Base de Cálculo do Financiamento')
                    ->options([
                        'avaliacao' => 'Baseado na Avaliação',
                        'imovel' => 'Baseado no Valor do Imóvel',
                    ])
                    ->value('avaliacao')
                    ->help('Escolha a base para o percentual de financiamento. Padrão: Avaliação.'),

                RadioButtons::make('fluxo.modo_calculo')
                    ->title('Modo de Cálculo do Financiamento')
                    ->options([
                        'percentual' => 'Calcular pelo Percentual',
                        'manual' => 'Definir Valor Manualmente',
                    ])
                    ->value('percentual')
                    ->id('modo_calculo')
                    ->help('Escolha como deseja calcular o valor do financiamento. Percentual é o padrão para automação.'),

                Group::make([
                    Input::make('fluxo.financiamento_percentual')
                        ->title('Financiamento (%)')
                        ->id('financiamento_percentual')
                        ->type('number')
                        ->min(10)
                        ->max(100)
                        ->step(1)
                        ->value(80)
                        ->help('Percentual de financiamento baseado na base selecionada. Mínimo 10%, máximo 100%.'),

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
                    ->help('Valores de FGTS, descontos e bônus aplicáveis. Reduz a entrada mínima.'),
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
                    ->help('Calculada automaticamente: Imóvel - Financiamento - Descontos. Deve ser positiva para viabilidade.'),
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
                        ->help('Valor pago na assinatura do contrato. Contribui para o total da entrada.'),

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
                        ->help('Valor pago na entrega das chaves. Parte dos pagamentos altos.'),
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
                        ->addButtonLabel('Add Balão')
                        ->addButtonIcon('bs.plus')
                        ->help('Adicione balões de pagamento personalizados. Datas e valores são opcionais, mas impactam o saldo.'),
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
                            ->help('Quantidade de parcelas da entrada. Deve ser positivo para cálculo.'),

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
                    ->help('Notas adicionais sobre este fluxo. Útil para detalhes personalizados ou integração com propostas.'),
            ]),

            Layout::view('orchid.fluxo-topo'),
            Layout::view('orchid.fluxo-calculo-js'),
        ];
    }

    /**
     * Salva o fluxo com parsing monetário e validações.
     */
    public function saveFluxo(Request $request)
    {
        $dados = $request->get('fluxo');
        $dados['status'] = $request->get('status', Fluxo::STATUS_COMPLETED);

        // === CORREÇÃO: Remove campo 'imovel' (não usado mais) ===
        unset($dados['imovel']);

        // Lista de campos monetários para parsing
        $monetaryFields = [
            'valor_imovel', 'valor_avaliacao', 'valor_financiado', 'valor_bonus_descontos',
            'entrada_minima', 'valor_assinatura_contrato', 'valor_na_chaves',
            'valor_parcela', 'total_parcelamento', 'valor_total_entrada', 'valor_restante',
        ];

        foreach ($monetaryFields as $field) {
            if (isset($dados[$field])) {
                $dados[$field] = $this->parseMonetary($dados[$field]);
            }
        }

        // Parsing para balões (array)
        if (isset($dados['baloes']) && is_array($dados['baloes'])) {
            foreach ($dados['baloes'] as &$balao) {
                if (isset($balao['valor'])) {
                    $balao['valor'] = $this->parseMonetary($balao['valor']);
                }
            }
        }

        // Validações de consistência
        $validator = Validator::make($dados, [
            'lead_id' => 'nullable|exists:leads,id',
            'valor_imovel' => 'required|numeric|min:0',
            'valor_avaliacao' => 'required|numeric|min:0',
            'modo_calculo' => 'required|in:percentual,manual',
            'base_calculo' => 'required|in:avaliacao,imovel',
            'financiamento_percentual' => 'required_if:modo_calculo,percentual|numeric|min:10|max:100',
            'entrada_minima' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            Toast::error('Erro de validação: ' . $validator->errors()->first());
            return;
        }

        // Salva o fluxo
        $fluxo = Fluxo::updateOrCreate(['id' => $dados['id'] ?? null], $dados);

        // === CORREÇÃO: Cria/atualiza Proposta ao salvar como COMPLETED ===
        if ($dados['status'] === Fluxo::STATUS_COMPLETED) {
            Proposta::updateOrCreate(
                ['fluxo_id' => $fluxo->id],
                [
                    'lead_id' => $fluxo->lead_id,
                    'valor_real' => $fluxo->valor_imovel,
                    'valor_entrada' => $fluxo->valor_total_entrada,
                    'valor_restante' => $fluxo->valor_restante,
                    'status' => 'ativa',
                ]
            );

            Toast::info('Fluxo salvo como Proposta! Acesse o menu Propostas.');
        } else {
            Toast::info('Fluxo salvo com sucesso!');
        }
    }

    /**
     * Função auxiliar para parse de valores BRL para decimal.
     */
    private function parseMonetary(string $value): float
    {
        if (empty($value)) {
            return 0.0;
        }
        $clean = str_replace(['R$', ' ', '.'], '', $value);
        $clean = str_replace(',', '.', $clean);
        return (float) $clean;
    }

    /**
     * Limpa o formulário recarregando a tela com um novo fluxo vazio.
     */
    public function resetFluxo()
    {
        Toast::info('Fluxo limpo.');
        return redirect()->route('platform.fluxo');
    }
}