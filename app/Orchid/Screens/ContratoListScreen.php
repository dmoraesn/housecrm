<?php

namespace App\Orchid\Screens;

use App\Models\Contrato;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ContratoListScreen extends Screen
{
    public function query(): array
    {
        return [
            'contratos' => Contrato::with(['lead', 'imovel', 'corretor'])
                ->orderBy('id', 'desc')
                ->paginate(15),
        ];
    }

    public function name(): string
    {
        return 'Contratos';
    }

    public function description(): string
    {
        return 'Listagem de contratos de compra e aluguel';
    }

    public function commandBar(): array
    {
        return [
            \Orchid\Screen\Actions\Link::make('Novo Contrato')
                ->icon('plus')
                ->route('platform.contratos.create'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('contratos', [
                TD::make('lead.nome', 'Lead')
                    ->render(fn($c) => $c->lead?->nome ?? '—'),
                TD::make('tipo', 'Tipo')
                    ->render(fn($c) => $c->tipo === 'compra' ? 'Compra' : 'Aluguel'),
                TD::make('valor_total', 'Valor Total')
                    ->render(fn($c) => 'R$ ' . number_format($c->valor_total, 2, ',', '.')),
                TD::make('data_assinatura', 'Assinatura')
                    ->render(fn($c) => $c->data_assinatura?->format('d/m/Y') ?? '—'),
                TD::make('status')
                    ->render(fn($c) => $c->getStatusBadge()),
                TD::make()
                    ->render(fn($c) => 
                        \Orchid\Screen\Actions\Link::make('Editar')
                            ->route('platform.contratos.edit', $c)
                            ->icon('pencil')
                    ),
            ]),
        ];
    }
}