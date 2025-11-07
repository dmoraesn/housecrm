<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Repository;

class ContratoListScreen extends Screen
{
    /**
     * Nome exibido no topo da tela.
     */
    public string $name = 'Contratos';

    /**
     * Descrição opcional abaixo do nome.
     */
    public string $description = 'Gestão e acompanhamento de contratos.';

    /**
     * Dados que serão passados para o layout (tabela, etc).
     */
    public function query(): iterable
    {
        // Aqui você traria os dados do seu modelo Contrato.
        // Exemplo: return ['contratos' => Contrato::paginate()];
        return [
            'contratos' => [
                new Repository(['id' => 1, 'cliente' => 'João Silva', 'valor' => 'R$ 250.000']),
                new Repository(['id' => 2, 'cliente' => 'Maria Oliveira', 'valor' => 'R$ 350.000']),
            ],
        ];
    }

    /**
     * Botões no topo da tela.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Novo Contrato')
                ->icon('plus')
                ->route('platform.contratos.create'),
        ];
    }

    /**
     * Layout principal da tela.
     */
    public function layout(): iterable
    {
        return [
            Layout::table('contratos', [
                TD::make('id', 'ID'),
                TD::make('cliente', 'Cliente')
                    ->render(fn($c) => Link::make($c->cliente)
                        ->route('platform.contratos.edit', $c->id)),
                TD::make('valor', 'Valor'),
            ]),
        ];
    }
}
