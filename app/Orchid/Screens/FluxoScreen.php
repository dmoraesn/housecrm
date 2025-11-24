<?php
declare(strict_types=1);
namespace App\Orchid\Screens;

use App\Models\Fluxo;
use App\Models\Lead;
use App\Models\Proposta;
use App\Models\Construtora;
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

class FluxoScreen extends Screen
{
    public $name = 'Plano de Pagamento de Entrada';
    public $description = 'Calculadora financeira com valores sempre do banco.';

    private const MONETARY_MASK = [
        'alias' => 'currency',
        'prefix' => 'R$ ',
        'groupSeparator' => '.',
        'radixPoint' => ',',
        'digits' => 2,
    ];

    private const READONLY_STYLE =
        'readonly-highlight bg-light border-primary fw-semibold text-primary';

    public function query(Fluxo $fluxo): array
    {
        if ($fluxo->exists) {
            $fluxo = $fluxo->fresh();
            $fluxo->load([
                'lead' => fn($q) => $q->where('ativo', true),
                'construtora' => fn($q) => $q->where('ativa', true),
            ]);
        } else {
            $fluxo->fill([
                'valor_imovel' => 0,
                'valor_avaliacao' => 0,
                'valor_bonus_descontos' => 0,
                'valor_financiado' => 0,
                'entrada_minima' => 0,
                'valor_assinatura_contrato' => 0,
                'valor_na_chaves' => 0,
                'parcelas_qtd' => 0,
                'valor_parcela' => 0,
                'total_parcelamento' => 0,
                'valor_total_entrada' => 0,
                'valor_restante' => 0,
                'base_calculo' => 'avaliacao',
                'modo_calculo' => 'manual',
                'financiamento_percentual' => 80,
                'baloes' => null,
            ]);
        }

        return [
            'fluxo' => $fluxo,
            'lead' => $fluxo->lead,
            'construtora' => $fluxo->construtora,
        ];
    }

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

    public function layout(): array
    {
        return [
            Layout::block(
                Layout::rows([
                    Relation::make('fluxo.lead_id')
                        ->title('Lead Associado')
                        ->fromModel(Lead::class, 'nome', 'id')
                        ->searchColumns('nome')
                        ->chunk(50)
                        ->placeholder('Opcional'),

                    Relation::make('fluxo.construtora_id')
                        ->title('Construtora')
                        ->fromModel(Construtora::class, 'nome', 'id')
                        ->searchColumns('nome')
                        ->chunk(50)
                        ->placeholder('Digite para buscar'),
                ])
            )
                ->title('Identificação')
                ->description('Selecione o lead e a construtora (Lead é opcional)')
                ->vertical(),

            Layout::block(
                Layout::rows([
                    Group::make([
                        Input::make('fluxo.valor_imovel')
                            ->title('Valor do Imóvel')
                            ->id('valor_imovel') // Mantido o ID para o JS
                            ->mask(self::MONETARY_MASK),

                        Input::make('fluxo.valor_avaliacao')
                            ->title('Valor de Avaliação')
                            ->id('valor_avaliacao') // Mantido o ID para o JS
                            ->mask(self::MONETARY_MASK),
                    ]),

                    RadioButtons::make('fluxo.base_calculo')
                        ->title('Base de Cálculo')
                        ->options([
                            'avaliacao' => 'Avaliação',
                            'imovel' => 'Imóvel'
                        ]),

                    RadioButtons::make('fluxo.modo_calculo')
                        ->title('Modo de Cálculo')
                        ->options([
                            'percentual' => 'Percentual',
                            'manual' => 'Manual'
                        ])
                        ->id('modo_calculo'), // Mantido o ID para o JS

                    Group::make([
                        Input::make('fluxo.financiamento_percentual')
                            ->title('Financiamento (%)')
                            ->id('financiamento_percentual') // Mantido o ID para o JS
                            ->type('number')
                            ->min(10)->max(100),

                        Input::make('fluxo.valor_financiado')
                            ->title('Valor Financiado')
                            ->id('valor_financiado') // Mantido o ID para o JS
                            ->mask(self::MONETARY_MASK),
                    ]),

                    Input::make('fluxo.valor_bonus_descontos')
                        ->title('Bônus / Descontos / FGTS')
                        ->id('valor_bonus_descontos') // Mantido o ID para o JS
                        ->mask(self::MONETARY_MASK),
                ])
            )
                ->title('1. Resumo do Imóvel e Financiamento')
                ->vertical(),

            Layout::block(
                Layout::rows([
                    Input::make('fluxo.entrada_minima')
                        ->title('Entrada Mínima Necessária')
                        ->id('entrada_minima') // Mantido o ID para o JS
                        ->readonly()
                        ->class(self::READONLY_STYLE . ' text-danger')
                        ->mask(self::MONETARY_MASK),
                ])
            )
                ->title('2. Entrada Mínima')
                ->vertical(),

            Layout::block(
                Layout::columns([
                    Layout::rows([
                        Input::make('fluxo.valor_assinatura_contrato')
                            ->title('Assinatura do Contrato (Sinal)') // Revertido para original (Sinal)
                            ->id('valor_assinatura_contrato') // Mantido o ID para o JS
                            ->mask(self::MONETARY_MASK),

                        Input::make('fluxo.valor_na_chaves')
                            ->title('Entrega das Chaves (Balanço)') // Revertido para original (Balanço)
                            ->id('valor_na_chaves') // Mantido o ID para o JS
                            ->mask(self::MONETARY_MASK),
                    ]),

                    Layout::rows([
                        Matrix::make('fluxo.baloes')
                            ->title('Balões de Pagamento') // Revertido para original (de Pagamento)
                            ->columns([
                                'Data' => 'data',
                                'Valor' => 'valor'
                            ])
                            ->fields([
                                'data' => Input::make()->type('date'),
                                'valor' => Input::make()->mask(self::MONETARY_MASK),
                            ])
                            ->addButtonLabel('Adicionar Balão')
                            ->addButtonIcon('bs.plus'),
                    ]),
                ])
            )
                ->title('3. Valores Altos e Balões')
                ->vertical(),

            Layout::block(
                Layout::columns([
                    Layout::rows([
                        Group::make([
                            Input::make('fluxo.parcelas_qtd')
                                ->title('Número de Parcelas') // Revertido para original (Número de)
                                ->id('parcelas_qtd') // Mantido o ID para o JS
                                ->type('number')
                                ->min(0),

                            Input::make('fluxo.valor_parcela')
                                ->title('Valor por Parcela')
                                ->id('valor_parcela') // Mantido o ID para o JS
                                ->readonly()
                                ->class(self::READONLY_STYLE)
                                ->mask(self::MONETARY_MASK),
                        ]),

                        Input::make('fluxo.total_parcelamento')
                            ->title('Total do Parcelamento') // Revertido para original (do)
                            ->id('total_parcelamento') // Mantido o ID para o JS
                            ->readonly()
                            ->class(self::READONLY_STYLE)
                            ->mask(self::MONETARY_MASK),
                    ]),

                    Layout::rows([
                        Input::make('fluxo.valor_total_entrada')
                            ->title('Valor Total da Entrada') // Revertido para original (Valor Da)
                            ->id('valor_total_entrada') // Mantido o ID para o JS
                            ->readonly()
                            ->class(self::READONLY_STYLE . ' text-success')
                            ->mask(self::MONETARY_MASK),

                        Input::make('fluxo.valor_restante')
                            ->title('Valor Restante (Diferença)') // Revertido para original (Valor (Diferença))
                            ->id('valor_restante') // Mantido o ID para o JS
                            ->readonly()
                            ->class(self::READONLY_STYLE . ' text-danger')
                            ->mask(self::MONETARY_MASK),
                    ]),
                ])
            )
                ->title('4. Parcelas e Resumo')
                ->vertical(),

            Layout::block(
                Layout::rows([
                    TextArea::make('fluxo.observacao')
                        ->title('Observações')
                        ->rows(5),

                    Input::make('fluxo.valor_assinatura_contrato_data')
                        ->type('hidden')
                        ->id('valor_assinatura_contrato_data') // Mantido o ID para o JS
                        ->set('data-default', now()->toDateString()),
                ])
            )
                ->title('5. Observações')
                ->vertical(),

            Layout::view('orchid.fluxo-topo'),
            Layout::view('orchid.fluxo-calculo-js'),
        ];
    }

    public function saveFluxo(Request $request)
    {
        $input = $request->get('fluxo', []);

        // Usa Fluxo::STATUS_DRAFT como default, como no Código 2, mas assegura a passagem do status
        $status = $request->input('status', Fluxo::STATUS_DRAFT);

        $editable = [
            'lead_id',
            'construtora_id',
            'valor_imovel',
            'valor_avaliacao',
            'base_calculo',
            'modo_calculo',
            'financiamento_percentual',
            'valor_financiado',
            'valor_bonus_descontos',
            'valor_assinatura_contrato',
            'valor_na_chaves',
            'parcelas_qtd',
            'observacao',
            'baloes',
            'valor_assinatura_contrato_data',
        ];

        $dados = array_intersect_key($input, array_flip($editable));

        // Refatoração concisa para parsear valores monetários (do Código 2)
        foreach ([
            'valor_imovel', 'valor_avaliacao', 'valor_financiado',
            'valor_bonus_descontos', 'valor_assinatura_contrato', 'valor_na_chaves',
        ] as $f) {
            if (isset($dados[$f])) {
                $dados[$f] = $this->parseMonetary($dados[$f]);
            }
        }

        // Refatoração concisa para parsear balões (do Código 2)
        if (!empty($dados['baloes']) && is_array($dados['baloes'])) {
            foreach ($dados['baloes'] as &$b) {
                if (isset($b['valor'])) {
                    $b['valor'] = $this->parseMonetary($b['valor']);
                }
            }
            unset($b);
        }

        $validator = Validator::make($dados, [
            'lead_id' => 'nullable|exists:leads,id',
            'construtora_id' => 'nullable|exists:construtoras,id',
            'valor_imovel' => 'required|numeric|min:1|max:50000000',
            'valor_avaliacao' => 'required|numeric|min:0|max:50000000',
            'base_calculo' => 'required|in:avaliacao,imovel',
            'modo_calculo' => 'required|in:percentual,manual',
            'financiamento_percentual' => 'required_if:modo_calculo,percentual|numeric|min:10|max:100',
            'parcelas_qtd' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            Toast::error($validator->errors()->first()); // Removido o 'Erro: ' do Código 1
            return;
        }

        $dados = $this->calcularFluxo($dados);

        $dataAssinatura = $dados['valor_assinatura_contrato_data'] ?? now()->toDateString();
        unset($dados['valor_assinatura_contrato_data']);

        $dados['status'] = $status;

        $fluxo = Fluxo::updateOrCreate(
            ['id' => $input['id'] ?? null],
            $dados
        );

        $propostaStatus = $status === Fluxo::STATUS_COMPLETED
            ? 'ativa'
            : 'rascunho';

        Proposta::updateOrCreate(
            ['fluxo_id' => $fluxo->id],
            [
                'lead_id' => $fluxo->lead_id,
                'construtora_id' => $fluxo->construtora_id,
                'valor_real' => $fluxo->valor_imovel,
                'valor_entrada' => $fluxo->valor_total_entrada,
                'valor_restante' => $fluxo->valor_restante,
                'data_assinatura' => $dataAssinatura,
                'status' => $propostaStatus,
            ]
        );

        // Refatoração concisa do Toast (do Código 2)
        Toast::info(
            $status === Fluxo::STATUS_COMPLETED
                ? 'Fluxo salvo como proposta! Acesse o menu Propostas.' // Retomado o texto completo do Código 1
                : 'Rascunho salvo com sucesso!' // Retomado o texto completo do Código 1
        );
    }

    private function calcularFluxo(array $dados): array
    {
        // Uso de variáveis mais curtas (do Código 2)
        $imovel = $dados['valor_imovel'] ?? 0;
        $avaliacao = $dados['valor_avaliacao'] ?? 0;
        $bonus = $dados['valor_bonus_descontos'] ?? 0;
        $assinatura = $dados['valor_assinatura_contrato'] ?? 0;
        $chaves = $dados['valor_na_chaves'] ?? 0;
        $parcelas = (int)($dados['parcelas_qtd'] ?? 0);
        $perc = $dados['financiamento_percentual'] ?? 80;
        $financiado = $dados['valor_financiado'] ?? 0;

        $base = $dados['base_calculo'] ?? 'avaliacao';
        $bus = $base === 'avaliacao' ? $avaliacao : $imovel; // $bus é a base de cálculo (do Código 2)
        $modo = $dados['modo_calculo'] ?? 'percentual';

        if ($modo === 'percentual' && $bus > 0) {
            $financiado = round($bus * ($perc / 100), 2);
        } elseif ($modo === 'manual' && $bus > 0) {
            // Reversa calcula % se o modo for manual (do Código 1/2)
            $perc = round(($financiado / $bus) * 100, 2);
        }

        // Cálculo da Entrada Mínima (Valor do Imóvel - Financiado - Bônus)
        $entrada = max(round($imovel - $financiado - $bonus, 2), 0);
        
        // Soma dos Balões (do Código 1/2)
        $totalBaloes = collect($dados['baloes'] ?? [])->sum(fn($b) => round($b['valor'] ?? 0, 2));

        // Valores já pagos (Assinatura + Chaves + Balões)
        $pagos = round($assinatura + $chaves + $totalBaloes, 2);
        
        // Restante a parcelar
        $resto = max($entrada - $pagos, 0);
        
        // Valores de Parcela e Total do Parcelamento (uso de variáveis curtas do Código 2)
        $valorParc = $parcelas > 0 ? round($resto / $parcelas, 2) : 0;
        $totalParc = round($valorParc * $parcelas, 2);
        
        // Total da Entrada e Saldo (diferença)
        $totalEnt = round($pagos + $totalParc, 2);
        $saldo = round($entrada - $totalEnt, 2);

        return [
            ...$dados, // Spread operator (do Código 2)
            'financiamento_percentual' => $perc,
            'valor_financiado' => $financiado,
            'entrada_minima' => $entrada,
            'valor_parcela' => $valorParc,
            'total_parcelamento' => $totalParc,
            'valor_total_entrada' => $totalEnt,
            'valor_restante' => $saldo,
        ];
    }

    private function parseMonetary($v): float
    {
        if (empty($v) || !is_scalar($v)) {
            return 0;
        }

        // Variável $v usada (do Código 2)
        $v = str_replace(['R$', ' ', '.'], '', (string)$v);
        return round((float)str_replace(',', '.', $v), 2);
    }

    public function resetFluxo()
    {
        Toast::info('Fluxo limpo.');
        return redirect()->route('platform.fluxo');
    }
}