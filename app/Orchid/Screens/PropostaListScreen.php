<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use App\Models\Proposta;

class PropostaListScreen extends Screen
{
    public $name = 'Propostas';
    public $description = 'Listagem e controle de propostas de imóveis';

    public function query(): array
    {
        return [
            'propostas' => Proposta::paginate(10),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Nova Proposta')
                ->icon('bs.plus-circle')
                ->route('platform.propostas.create'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('propostas', [
                TD::make('id', 'ID')->sort(),
                TD::make('cliente', 'Cliente'),
                TD::make('imovel', 'Imóvel'),
                TD::make('valor', 'Valor')->render(fn ($p) => 'R$ ' . number_format($p->valor, 2, ',', '.')),
                TD::make('status', 'Status')->render(fn ($p) => ucfirst($p->status)),
                TD::make('data_envio', 'Data de Envio')->render(fn ($p) => $p->data_envio?->format('d/m/Y')),
                TD::make('Ações')->align(TD::ALIGN_CENTER)->render(fn ($p) =>
                    Link::make('Editar')
                        ->icon('bs.pencil')
                        ->route('platform.propostas.edit', $p->id)
                ),
            ]),
        ];
    }
}
