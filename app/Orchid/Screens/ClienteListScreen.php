<?php

namespace App\Orchid\Screens;

use App\Models\Cliente; // Importação mantida
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ClienteListScreen extends Screen
{
    public string $name = 'Lista de Clientes';
    public string $description = 'Gerenciamento de clientes do CRM.';

    /**
     * @return iterable
     */
    public function query(): iterable
    {
        // Define os dados de simulação
        $data = [
            (object) ['id' => 1, 'nome' => 'Carlos Souza', 'email' => 'carlos@email.com', 'telefone' => '(85) 99999-1111'],
            (object) ['id' => 2, 'nome' => 'Ana Lima', 'email' => 'ana@email.com', 'telefone' => '(85) 98888-2222'],
            (object) ['id' => 3, 'nome' => 'João Silva', 'email' => 'joao@email.com', 'telefone' => '(11) 97777-3333'],
            (object) ['id' => 4, 'nome' => 'Maria Oliveira', 'email' => 'maria@email.com', 'telefone' => '(21) 96666-4444'],
        ];

        // Retornamos a Collection de objetos.
        return [
            'clientes' => collect($data), 
        ];

        /*
         * PRÓXIMO PASSO: Usar o modelo Eloquent real (Cliente::filters()->paginate())
         */
    }

    /**
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            Layout::table('clientes', [
                // CORREÇÃO: Forçar a renderização para string em todas as colunas
                // Isso evita que o Orchid tente chamar getContent() no objeto stdClass
                TD::make('id', 'ID')
                    ->render(fn ($cliente) => (string) $cliente->id),
                TD::make('nome', 'Nome')
                    ->render(fn ($cliente) => (string) $cliente->nome),
                TD::make('email', 'Email')
                    ->render(fn ($cliente) => (string) $cliente->email),
                TD::make('telefone', 'Telefone')
                    ->render(fn ($cliente) => (string) $cliente->telefone),
                
                // Exemplo de coluna de link (que naturalmente retorna um objeto compatível)
                // TD::make('actions', 'Ações')
                //     ->render(fn ($cliente) => Link::make('Editar')->route('platform.clientes.edit', $cliente->id)),
            ]),
        ];
    }
}