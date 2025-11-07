<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Repository;
use Orchid\Screen\Actions\Link;

class AluguelListScreen extends Screen
{
    public string $name = 'Aluguéis';
    public string $description = 'Listagem e controle de contratos de aluguel.';

    public function query(): iterable
    {
        // Exemplo estático — substitua por Aluguel::paginate() futuramente
        return [
            'alugueis' => [
                new Repository(['id' => 1, 'inquilino' => 'Carlos Souza', 'valor' => 'R$ 1.500']),
                new Repository(['id' => 2, 'inquilino' => 'Fernanda Lima', 'valor' => 'R$ 2.100']),
            ],
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Novo Aluguel')
                ->icon('plus')
                ->route('platform.alugueis.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('alugueis', [
                TD::make('id', 'ID'),
                TD::make('inquilino', 'Inquilino')->render(fn($a) =>
                    Link::make($a->inquilino)
                        ->route('platform.alugueis.edit', $a->id)
                ),
                TD::make('valor', 'Valor'),
            ]),
        ];
    }
}
