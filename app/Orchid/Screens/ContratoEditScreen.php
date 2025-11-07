<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Facades\Layout;

class ContratoEditScreen extends Screen
{
    public string $name = 'Editar Contrato';
    public string $description = 'Cadastro e edição de contratos';

    public function query(): iterable
    {
        return [];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('cliente')
                    ->title('Cliente')
                    ->placeholder('Nome do cliente'),

                Input::make('valor')
                    ->title('Valor do contrato')
                    ->placeholder('Ex: R$ 250.000'),
            ]),
        ];
    }
}
