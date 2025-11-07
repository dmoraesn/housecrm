<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Button;
use Illuminate\Http\Request;

class ConstrutoraEditScreen extends Screen
{
    public $name = 'Cadastrar / Editar Construtora';
    public $description = 'Cadastro e edição de construtoras';

    public function query(): iterable
    {
        return [];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Salvar')
                ->icon('check')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('nome')
                    ->title('Nome')
                    ->placeholder('Digite o nome da construtora')
                    ->required(),

                Input::make('email')
                    ->title('Email')
                    ->placeholder('contato@exemplo.com'),

                Input::make('telefone')
                    ->title('Telefone')
                    ->placeholder('(99) 99999-9999'),
            ]),
        ];
    }

    public function save(Request $request)
    {
        // Aqui virá a lógica de salvar no banco (posteriormente)
        \Orchid\Support\Facades\Alert::info('Construtora salva com sucesso!');
    }
}
