<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;

class AluguelEditScreen extends Screen
{
    public string $name = 'Editar Aluguel';
    public string $description = 'Cadastro e edição de contratos de aluguel.';

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
                Input::make('inquilino')->title('Inquilino')->placeholder('Nome do inquilino'),
                Input::make('valor')->title('Valor do aluguel')->placeholder('Ex: R$ 1.500'),
            ]),
        ];
    }
}
