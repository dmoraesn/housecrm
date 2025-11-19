<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Construtora;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Dropdown; // Novo
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ConstrutoraListScreen extends Screen
{
    public $name = 'Construtoras';
    public $description = 'Gerenciamento de construtoras e parceiros';

    public function query(): array
    {
        return [
            'construtoras' => Construtora::filters()
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    public function commandBar(): array
    {
        return [
            // Substituído o Link por um Dropdown para oferecer as duas opções
            Dropdown::make('Adicionar Nova')
                ->icon('bs.plus-circle')
                ->list([
                    Link::make('Automático (Buscar CNPJ)')
                        ->icon('bs.search')
                        ->route('platform.construtoras.create.auto'),

                    Link::make('Manual')
                        ->icon('bs.pencil')
                        ->route('platform.construtoras.create.manual'),
                ]),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('construtoras', [
                TD::make('nome', 'Razão Social')
                    ->width('300px')      // Largura fixa para evitar quebra excessiva
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn(Construtora $c) => Link::make($c->nome)
                        ->route('platform.construtoras.edit', $c)),

                TD::make('cnpj', 'CNPJ')
                    ->width('150px')
                    ->alignCenter()
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn(Construtora $c) => $c->cnpj_formatted
                        ? "<code>{$c->cnpj_formatted}</code>"
                        : '<em>—</em>'),

                TD::make('telefone', 'Telefone')
                    ->width('140px')
                    ->alignCenter()
                    ->render(fn(Construtora $c) => $c->telefone_formatted ?? '<em>—</em>'),

                TD::make('email', 'E-mail')
                    ->width('200px')
                    ->render(fn(Construtora $c) => $c->email
                        ? "<a href='mailto:{$c->email}'>{$c->email}</a>"
                        : '<em>—</em>'),

                TD::make('status', 'Status')
                    ->width('100px')
                    ->alignCenter()
                    ->render(fn(Construtora $c) => $c->status
                        ? '<span class="badge bg-success">Ativa</span>'
                        : '<span class="badge bg-secondary">Inativa</span>'),

                TD::make('created_at', 'Criada em')
                    ->width('130px')
                    ->alignCenter()
                    ->sort()
                    ->render(fn(Construtora $c) => $c->created_at->format('d/m/Y')),
            ]),
        ];
    }
}