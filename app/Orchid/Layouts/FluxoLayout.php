<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Matrix;

class FluxoLayout extends Rows
{
    protected function fields(): array
    {
        return [
            Relation::make('cliente_id')
                ->fromModel(\App\Models\Cliente::class, 'nome')
                ->title('Cliente')
                ->help('Selecione o cliente associado ao fluxo.'),

            Input::make('valor_imovel')
                ->title('Valor do Imóvel')
                ->mask([
                    'alias' => 'currency',
                    'prefix' => 'R$ ',
                    'digitsOptional' => false,
                ])
                ->required(),

            Input::make('valor_avaliacao')
                ->title('Valor de Avaliação')
                ->mask(['alias' => 'currency', 'prefix' => 'R$ ']),

            Input::make('valor_entrada')
                ->title('Valor da Entrada')
                ->mask(['alias' => 'currency', 'prefix' => 'R$ ']),

            Input::make('valor_bonus_descontos')
                ->title('Bônus / Descontos / FGTS')
                ->mask(['alias' => 'currency', 'prefix' => 'R$ ']),

            Input::make('valor_assinatura_contrato')
                ->title('Assinatura do Contrato')
                ->mask(['alias' => 'currency', 'prefix' => 'R$ ']),

            Input::make('valor_na_chaves')
                ->title('Valor nas Chaves')
                ->mask(['alias' => 'currency', 'prefix' => 'R$ ']),

            Matrix::make('baloes')
                ->title('Balões de Pagamento')
                ->columns([
                    'Data' => 'data',
                    'Valor' => 'valor',
                ])
                ->fields([
                    'data' => Input::make()->type('date'),
                    'valor' => Input::make()->type('number')->step(0.01),
                ])
                ->help('Adicione ou remova balões de pagamento conforme necessário.'),

            Input::make('parcelas_qtd')
                ->title('Número de Parcelas')
                ->type('number'),

            Input::make('valor_parcela')
                ->title('Valor da Parcela')
                ->mask(['alias' => 'currency', 'prefix' => 'R$ ']),

            Input::make('total_parcelamento')
                ->title('Total Parcelamento')
                ->mask(['alias' => 'currency', 'prefix' => 'R$ ']),

            TextArea::make('observacao')
                ->title('Observações'),
        ];
    }
}
