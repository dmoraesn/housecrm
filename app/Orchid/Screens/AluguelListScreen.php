<?php

namespace App\Orchid\Screens;

use App\Models\Aluguel;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class AluguelListScreen extends Screen
{
    public function query(): array
    {
        return [
            'alugueis' => Aluguel::with(['imovel', 'inquilino', 'corretor'])
                ->orderBy('id', 'desc')
                ->paginate(15),
        ];
    }

    public function name(): string
    {
        return 'Aluguéis';
    }

    public function description(): string
    {
        return 'Listagem de contratos de aluguel';
    }

    public function commandBar(): array
    {
        return [
            \Orchid\Screen\Actions\Link::make('Novo Aluguel')
                ->icon('plus')
                ->route('platform.alugueis.create'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('alugueis', [
                TD::make('imovel.titulo', 'Imóvel')
                    ->render(fn($a) => $a->imovel?->titulo ?? '—'),
                TD::make('inquilino.name', 'Inquilino')
                    ->render(fn($a) => $a->inquilino?->name ?? '—'),
                TD::make('valor_mensal', 'Valor Mensal')
                    ->render(fn($a) => 'R$ ' . number_format($a->valor_mensal, 2, ',', '.')),
                TD::make('data_inicio', 'Início')
                    ->render(fn($a) => $a->data_inicio?->format('d/m/Y') ?? '—'),
                TD::make('status')
                    ->render(fn($a) => $a->getStatusBadge()),
                TD::make()
                    ->render(fn($a) => 
                        \Orchid\Screen\Actions\Link::make('Editar')
                            ->route('platform.alugueis.edit', $a)
                            ->icon('pencil')
                    ),
            ]),
        ];
    }
}