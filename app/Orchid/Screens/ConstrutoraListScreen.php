<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;

class ConstrutoraListScreen extends Screen
{
    public $name = 'Construtoras';
    public $description = 'Lista de Construtoras cadastradas';

    public function query(): iterable
    {
        // Aqui você listaria os dados vindos do modelo
        return [
            'construtoras' => [],
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Adicionar Construtora')
                ->icon('plus')
                ->route('platform.construtoras.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('construtoras', [
                TD::make('id', 'ID'),
                TD::make('nome', 'Nome'),
                TD::make('email', 'Email'),
                TD::make('telefone', 'Telefone'),
            ]),
        ];
    }
}
