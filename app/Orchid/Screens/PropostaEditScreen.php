<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use App\Models\Proposta;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;

class PropostaEditScreen extends Screen
{
    public $name = 'Cadastro de Proposta';
    public $description = 'Criação ou edição de propostas de compra ou locação';
    public ?Proposta $proposta = null;

    public function query(Proposta $proposta): array
    {
        return ['proposta' => $proposta];
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
                Input::make('proposta.cliente')->title('Cliente')->required(),
                Input::make('proposta.imovel')->title('Imóvel')->required(),
                Input::make('proposta.valor')->title('Valor')->mask([
                    'alias' => 'currency',
                    'prefix' => 'R$ ',
                    'autoUnmask' => true
                ]),
                Select::make('proposta.status')->title('Status')->options([
                    'aberta' => 'Aberta',
                    'aceita' => 'Aceita',
                    'recusada' => 'Recusada',
                    'cancelada' => 'Cancelada',
                ]),
                Input::make('proposta.data_envio')->title('Data de Envio')->type('date'),
            ]),
        ];
    }

    public function save(Request $request, Proposta $proposta)
    {
        $proposta->fill($request->get('proposta'))->save();
        Toast::info('Proposta salva com sucesso!');
        return redirect()->route('platform.propostas');
    }
}
