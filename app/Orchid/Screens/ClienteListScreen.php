<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ClienteListScreen extends Screen
{
    public string $name = 'Lista de Clientes';
    public string $description = 'Gerenciamento de clientes do CRM.';

    public function query(): iterable
    {
        return [
            'clientes' => [
                ['id' => 1, 'nome' => 'Carlos Souza', 'email' => 'carlos@email.com', 'telefone' => '(85) 99999-1111'],
                ['id' => 2, 'nome' => 'Ana Lima', 'email' => 'ana@email.com', 'telefone' => '(85) 98888-2222'],
            ],
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('clientes', [
                TD::make('id', 'ID'),
                TD::make('nome', 'Nome'),
                TD::make('email', 'Email'),
                TD::make('telefone', 'Telefone'),
            ]),
        ];
    }
}
