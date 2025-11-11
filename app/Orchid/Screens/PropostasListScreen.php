<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Illuminate\Support\HtmlString;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use App\Models\Proposta;

class PropostasListScreen extends Screen
{
    public $name = 'Propostas de Pagamento';
    public $description = 'Gerenciamento de propostas.';

    public function commandBar(): array
    {
        return [];
    }

    public function query(): array
    {
        return [
            'propostas' => Proposta::with('lead')
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('propostas', [
                TD::make('id', 'ID')
                    ->sort()
                    ->render(fn($p) => Link::make($p->id)->route('platform.propostas.edit', $p)),

                TD::make('lead.nome', 'Cliente')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn($p) => $p->lead
                        ? Link::make($p->lead->nome)->route('platform.leads.edit', $p->lead)
                        : 'N/A'),

                TD::make('valor_real', 'Valor do Bem')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn($p) => 'R$ ' . number_format($p->valor_real ?? 0, 2, ',', '.')),

                TD::make('valor_entrada', 'Entrada')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn($p) => 'R$ ' . number_format($p->valor_entrada ?? 0, 2, ',', '.')),

                TD::make('valor_restante', 'Diferença')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(function ($p) {
                        $v = $p->valor_restante ?? 0;
                        $c = $v > 0.01 ? 'text-danger' : ($v < -0.01 ? 'text-warning' : 'text-success');
                        $html = "<span class='{$c} font-weight-bold'>R$ " . number_format($v, 2, ',', '.') . "</span>";
                        return new HtmlString($html);
                    }),

                TD::make('created_at', 'Criada')
                    ->sort()
                    ->render(fn($p) => $p->created_at->format('d/m/Y H:i')),
            ]),
        ];
    }
}
