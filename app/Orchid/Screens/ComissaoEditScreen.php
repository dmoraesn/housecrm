<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use App\Models\Comissao;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;

class ComissaoEditScreen extends Screen
{
    public $name = 'Cadastro de Comissão';
    public $description = 'Criação ou edição de comissões';
    public ?Comissao $comissao = null;

    public function query(Comissao $comissao): array
    {
        return ['comissao' => $comissao];
    }

    public function commandBar(): array
    {
        return [
            \Orchid\Screen\Actions\Button::make('Salvar')
                ->icon('bs.check')
                ->method('save'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                Input::make('comissao.corretor')->title('Nome do Corretor')->required(),
                Input::make('comissao.imovel')->title('Imóvel')->required(),
                Input::make('comissao.valor')->title('Valor da Comissão')->mask([
                    'alias' => 'currency',
                    'prefix' => 'R$ ',
                    'autoUnmask' => true
                ]),
                Input::make('comissao.percentual')->title('Percentual (%)')->type('number'),
                Select::make('comissao.status')->title('Status')->options([
                    'pendente' => 'Pendente',
                    'pago' => 'Pago',
                    'cancelado' => 'Cancelado',
                ]),
                Input::make('comissao.data_pagamento')->title('Data de Pagamento')->type('date'),
            ]),
        ];
    }

    public function save(Request $request, Comissao $comissao)
    {
        $comissao->fill($request->get('comissao'))->save();
        Toast::info('Comissão salva com sucesso!');
        return redirect()->route('platform.comissoes');
    }
}
