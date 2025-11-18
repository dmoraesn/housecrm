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
    private const READONLY_STYLE = 'readonly-highlight bg-light border-primary fw-semibold text-primary';

    public function query(Fluxo $fluxo): array
    {
        if ($fluxo->exists) {
            $fluxo = $fluxo->fresh();
            $fluxo->load(['lead' => fn($query) => $query->where('ativo', true), 'construtora' => fn($query) => $query->where('ativa', true)]);
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
            'lead' => $fluxo->lead ?? null,
            'construtora' => $fluxo->construtora ?? null,
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
                        ->placeholder('Digite para buscar o lead'),
                    Relation::make('fluxo.construtora_id')
                        ->title('Construtora')
                        ->fromModel(Construtora::class, 'nome', 'id')
                        ->searchColumns('nome')
                        ->chunk(50)
                        ->placeholder('Digite para buscar a construtora'),
                ])
            )
                ->title('Identificação')
                ->description('Selecione o lead e a construtora')
                ->vertical(),
            Layout::block(
                Layout::rows([
                    Group::make([
                        Input::make('fluxo.valor_imovel')
                            ->title('Valor do Imóvel')
                            ->id('valor_imovel')
                            ->mask(self::MONETARY_MASK),
                        Input::make('fluxo.valor_avaliacao')
                            ->title('Valor de Avaliação')
                            ->id('valor_avaliacao')
                            ->mask(self::MONETARY_MASK),
                    ]),
                    RadioButtons::make('fluxo.base_calculo')
                        ->title('Base de Cálculo')
                        ->options(['avaliacao' => 'Avaliação', 'imovel' => 'Imóvel']),
                    RadioButtons::make('fluxo.modo_calculo')
                        ->title('Modo de Cálculo')
                        ->options(['percentual' => 'Percentual', 'manual' => 'Manual'])
                        ->id('modo_calculo'),
                    Group::make([
                        Input::make('fluxo.financiamento_percentual')
                            ->title('Financiamento (%)')
                            ->id('financiamento_percentual')
                            ->type('number')
                            ->min(10)->max(100),
                        Input::make('fluxo.valor_financiado')
                            ->title('Valor Financiado')
                            ->id('valor_financiado')
                            ->mask(self::MONETARY_MASK),
                    ]),
                    Input::make('fluxo.valor_bonus_descontos')
                        ->title('Bônus / Descontos / FGTS')
                        ->id('valor_bonus_descontos')
                        ->mask(self::MONETARY_MASK),
                ])
            )
                ->title('1. Resumo do Imóvel e Financiamento')
                ->vertical(),
            Layout::block(
                Layout::rows([
                    Input::make('fluxo.entrada_minima')
                        ->title('Entrada Mínima Necessária')
                        ->id('entrada_minima')
                        ->readonly()
                        ->class(self::READONLY_STYLE.' text-danger')
                        ->mask(self::MONETARY_MASK),
                ])
            )
                ->title('2. Entrada Mínima')
                ->vertical(),
            Layout::block(
                Layout::columns([
                    Layout::rows([
                        Input::make('fluxo.valor_assinatura_contrato')
                            ->title('Assinatura do Contrato (Sinal)')
                            ->id('valor_assinatura_contrato')
                            ->mask(self::MONETARY_MASK),
                        Input::make('fluxo.valor_na_chaves')
                            ->title('Entrega das Chaves (Balanço)')
                            ->id('valor_na_chaves')
                            ->mask(self::MONETARY_MASK),
                    ]),
                    Layout::rows([
                        Matrix::make('fluxo.baloes')
                            ->title('Balões de Pagamento')
                            ->columns([
                                'Data'  => 'data',
                                'Valor' => 'valor'
                            ])
                            ->fields([
                                'data'  => Input::make()->type('date'),
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
                                ->title('Número de Parcelas')
                                ->id('parcelas_qtd')
                                ->type('number')
                                ->min(0),
                            Input::make('fluxo.valor_parcela')
                                ->title('Valor por Parcela')
                                ->id('valor_parcela')
                                ->readonly()
                                ->class(self::READONLY_STYLE)
                                ->mask(self::MONETARY_MASK),
                        ]),
                        Input::make('fluxo.total_parcelamento')
                            ->title('Total do Parcelamento')
                            ->id('total_parcelamento')
                            ->readonly()
                            ->class(self::READONLY_STYLE)
                            ->mask(self::MONETARY_MASK),
                    ]),
                    Layout::rows([
                        Input::make('fluxo.valor_total_entrada')
                            ->title('Valor Total da Entrada')
                            ->id('valor_total_entrada')
                            ->readonly()
                            ->class(self::READONLY_STYLE.' text-success')
                            ->mask(self::MONETARY_MASK),
                        Input::make('fluxo.valor_restante')
                            ->title('Valor Restante (Diferença)')
                            ->id('valor_restante')
                            ->readonly()
                            ->class(self::READONLY_STYLE.' text-danger')
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
                        ->id('valor_assinatura_contrato_data')
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
        $input  = $request->get('fluxo', []);
        $status = $request->input('status', Fluxo::STATUS_COMPLETED);
        $editable = [
            'lead_id', 'construtora_id', 'valor_imovel', 'valor_avaliacao',
            'base_calculo', 'modo_calculo', 'financiamento_percentual',
            'valor_financiado', 'valor_bonus_descontos', 'valor_assinatura_contrato',
            'valor_na_chaves', 'parcelas_qtd', 'observacao', 'baloes',
            'valor_assinatura_contrato_data',
        ];
        $dados = array_intersect_key($input, array_flip($editable));
        $monetary = [
            'valor_imovel', 'valor_avaliacao', 'valor_financiado',
            'valor_bonus_descontos', 'valor_assinatura_contrato', 'valor_na_chaves',
        ];
        foreach ($monetary as $field) {
            if (array_key_exists($field, $dados)) {
                $dados[$field] = $this->parseMonetary($dados[$field]);
            }
        }
        if (isset($dados['baloes']) && is_array($dados['baloes'])) {
            foreach ($dados['baloes'] as &$balao) {
                if (isset($balao['valor'])) {
                    $balao['valor'] = $this->parseMonetary($balao['valor']);
                }
            }
            unset($balao);
        }
        $validator = Validator::make($dados, [
            'lead_id'            => 'nullable|exists:leads,id',
            'construtora_id'     => 'nullable|exists:construtoras,id',
            'valor_imovel'       => 'required|numeric|min:1|max:50000000',
            'valor_avaliacao'    => 'required|numeric|min:0|max:50000000',
            'base_calculo'       => 'required|in:avaliacao,imovel',
            'modo_calculo'       => 'required|in:percentual,manual',
            'financiamento_percentual' => 'required_if:modo_calculo,percentual|numeric|min:10|max:100',
            'parcelas_qtd'       => 'nullable|integer|min:0',
        ]);
        if ($validator->fails()) {
            Toast::error('Erro: ' . $validator->errors()->first());
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
        if ($status === Fluxo::STATUS_COMPLETED) {
            Proposta::updateOrCreate(
                ['fluxo_id' => $fluxo->id],
                [
                    'lead_id'                   => $fluxo->lead_id,
                    'construtora_id'            => $fluxo->construtora_id,
                    'valor_real'                => $fluxo->valor_imovel,
                    'valor_entrada'             => $fluxo->valor_total_entrada,
                    'valor_restante'            => $fluxo->valor_restante,
                    'data_assinatura'           => $dataAssinatura,
                    'status'                    => 'ativa',
                ]
            );
            Toast::info('Fluxo salvo como Proposta! Acesse o menu Propostas.');
        } else {
            Toast::info('Rascunho salvo com sucesso!');
        }
    }

    private function calcularFluxo(array $dados): array
    {
        $imovel     = $dados['valor_imovel'] ?? 0.0;
        $avaliacao  = $dados['valor_avaliacao'] ?? 0.0;
        $bonus      = $dados['valor_bonus_descontos'] ?? 0.0;
        $assinatura = $dados['valor_assinatura_contrato'] ?? 0.0;
        $chaves     = $dados['valor_na_chaves'] ?? 0.0;
        $parcelas   = (int)($dados['parcelas_qtd'] ?? 0);
        $perc       = (float)($dados['financiamento_percentual'] ?? 80.0);
        $financiado = $dados['valor_financiado'] ?? 0.0;
        $modo       = $dados['modo_calculo'] ?? 'percentual';
        $base       = $dados['base_calculo'] ?? 'avaliacao';
        $baseValor = $base === 'avaliacao' ? $avaliacao : $imovel;
        if ($modo === 'percentual' && $baseValor > 0) {
            $financiado = round($baseValor * ($perc / 100), 2);
        } elseif ($modo === 'manual' && $baseValor > 0) {
            $perc = round(($financiado / $baseValor) * 100, 2);
        }
        $entrada = max(round($imovel - $financiado - $bonus, 2), 0);
        $totalBaloes = collect($dados['baloes'] ?? [])
            ->sum(fn($b) => round($b['valor'] ?? 0, 2));
        $pagos = round($assinatura + $chaves + $totalBaloes, 2);
        $resto = max($entrada - $pagos, 0);
        $valorParcela = $parcelas > 0 ? round($resto / $parcelas, 2) : 0.0;
        $totalParcelamento = round($valorParcela * $parcelas, 2);
        $totalEntrada = round($pagos + $totalParcelamento, 2);
        $saldo = round($entrada - $totalEntrada, 2);
        return array_merge($dados, [
            'financiamento_percentual' => $perc,
            'valor_financiado'         => $financiado,
            'entrada_minima'           => $entrada,
            'valor_parcela'            => $valorParcela,
            'total_parcelamento'       => $totalParcelamento,
            'valor_total_entrada'      => $totalEntrada,
            'valor_restante'           => $saldo,
        ]);
    }

    private function parseMonetary($value): float
    {
        if (empty($value) || !is_scalar($value)) {
            return 0.0;
        }
        $value = (string)$value;
        $clean = str_replace(['R$', ' ', '.'], '', $value);
        $clean = str_replace(',', '.', $clean);
        return round((float)$clean, 2);
    }

    public function resetFluxo()
    {
        Toast::info('Fluxo limpo.');
        return redirect()->route('platform.fluxo');
    }
}