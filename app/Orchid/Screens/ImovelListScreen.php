<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ImovelListScreen extends Screen
{
    /**
     * Nome da tela
     */
    public string $name = 'Lista de Imóveis';

    /**
     * Descrição
     */
    public string $description = 'Gerenciamento de imóveis cadastrados no sistema.';

    /**
     * Dados enviados para a tela
     */
    public function query(): iterable
    {
        // Poderia retornar dados reais do banco futuramente
        return [
            'imoveis' => [
                ['id' => 1, 'titulo' => 'Apartamento Beira-Mar', 'cidade' => 'Fortaleza', 'valor' => 'R$ 850.000'],
                ['id' => 2, 'titulo' => 'Casa no Eusébio', 'cidade' => 'Eusébio', 'valor' => 'R$ 690.000'],
            ],
        ];
    }

    /**
     * Layout da tela
     */
    public function layout(): iterable
    {
        return [
            Layout::table('imoveis', [
                TD::make('id', 'ID'),
                TD::make('titulo', 'Título'),
                TD::make('cidade', 'Cidade'),
                TD::make('valor', 'Valor'),
            ]),
        ];
    }
}
