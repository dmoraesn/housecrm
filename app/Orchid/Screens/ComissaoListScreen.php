<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use App\Models\Comissao;

class ComissaoListScreen extends Screen
{
    public $name = 'Comissões';
    public $description = 'Painel de controle das comissões dos corretores e imobiliárias';

    public function query(): array
    {
        return [
            'comissoes' => Comissao::paginate(10),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Nova Comissão')
                ->icon('bs.plus-circle')
                ->route('platform.comissoes.create'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('comissoes', [
                TD::make('id', 'ID')->sort(),
                TD::make('corretor', 'Corretor')->render(fn ($c) => e($c->corretor)),
                TD::make('imovel', 'Imóvel')->render(fn ($c) => e($c->imovel)),
                TD::make('valor', 'Valor')->render(fn ($c) => 'R$ ' . number_format($c->valor, 2, ',', '.')),
                TD::make('percentual', 'Percentual (%)'),
                TD::make('status', 'Status'),
                TD::make('data_pagamento', 'Data de Pagamento')->render(fn ($c) => $c->data_pagamento?->format('d/m/Y')),
                TD::make('Ações')->align(TD::ALIGN_CENTER)->render(fn ($c) =>
                    Link::make('Editar')
                        ->icon('bs.pencil')
                        ->route('platform.comissoes.edit', $c->id)
                ),
            ]),
        ];
    }
}
