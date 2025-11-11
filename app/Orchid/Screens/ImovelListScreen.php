<?php

namespace App\Orchid\Screens;

use App\Models\Imovel;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ImovelListScreen extends Screen
{
    public function query(): array
    {
        return [
            'imoveis' => Imovel::with(['construtora', 'corretor'])
                ->orderBy('id', 'desc') // ← CORRIGIDO
                ->paginate(15),
        ];
    }

    public function name(): string
    {
        return 'Imóveis';
    }

    public function description(): string
    {
        return 'Listagem completa de imóveis';
    }

    public function commandBar(): array
    {
        return [
            \Orchid\Screen\Actions\Link::make('Novo Imóvel')
                ->icon('plus')
                ->route('platform.imoveis.create'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('imoveis', [
                TD::make('titulo', 'Título')->sort(),
                TD::make('tipo', 'Tipo'),
                TD::make('valor_venda', 'Venda')
                    ->render(fn($i) => $i->valor_venda ? 'R$ ' . number_format($i->valor_venda, 2, ',', '.') : '—'),
                TD::make('valor_aluguel', 'Aluguel')
                    ->render(fn($i) => $i->valor_aluguel ? 'R$ ' . number_format($i->valor_aluguel, 2, ',', '.') : '—'),
                TD::make('construtora.nome', 'Construtora')
                    ->render(fn($i) => $i->construtora?->nome ?? '—'),
                TD::make('corretor.name', 'Corretor')
                    ->render(fn($i) => $i->corretor?->name ?? '—'),
                TD::make('status')
                    ->render(fn($i) => $i->getStatusBadge()),
                TD::make('created_at', 'Criado em')
                    ->render(fn($i) => $i->created_at->format('d/m/Y')),
                TD::make()
                    ->render(fn($i) => 
                        \Orchid\Screen\Actions\Link::make('Editar')
                            ->route('platform.imoveis.edit', $i)
                            ->icon('pencil')
                    ),
            ]),
        ];
    }
}