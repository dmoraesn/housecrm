<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ImovelEditScreen extends Screen
{
    public string $name = 'Cadastro de Imóvel';
    public string $description = 'Criação e edição de imóveis cadastrados no sistema.';

    public function query(): iterable
    {
        // Aqui você pode buscar o imóvel do banco futuramente
        return [];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Salvar')
                ->icon('check')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('imovel.titulo')
                    ->title('Título')
                    ->placeholder('Ex: Apartamento Beira-Mar'),

                Input::make('imovel.cidade')
                    ->title('Cidade')
                    ->placeholder('Ex: Fortaleza'),

                Input::make('imovel.valor')
                    ->title('Valor')
                    ->placeholder('Ex: R$ 850.000'),
            ]),
        ];
    }

    public function save()
    {
        Toast::info('Imóvel salvo com sucesso!');
    }
}
