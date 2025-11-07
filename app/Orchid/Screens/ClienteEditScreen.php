<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ClienteEditScreen extends Screen
{
    public string $name = 'Cadastro de Cliente';
    public string $description = 'Criar ou editar um cliente.';

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
                Input::make('cliente.nome')
                    ->title('Nome')
                    ->placeholder('Digite o nome completo'),

                Input::make('cliente.email')
                    ->title('E-mail')
                    ->placeholder('Digite o e-mail'),

                Input::make('cliente.telefone')
                    ->title('Telefone')
                    ->placeholder('(xx) xxxxx-xxxx'),
            ]),
        ];
    }

    public function save()
    {
        Toast::info('Cliente salvo com sucesso!');
    }
}
