<?php

namespace App\Orchid\Screens;

use App\Models\Proposta;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;

class PropostasArquivadasScreen extends PropostasListScreen
{
    public $name = 'Propostas Arquivadas';
    public $description = 'Histórico de propostas arquivadas.';

    /**
     * Query: apenas propostas arquivadas
     */
    public function query(): array
    {
        return [
            'propostas' => Proposta::with('lead')
                ->filters()
                ->where('status', 'arquivada')
                ->orderBy('id', 'desc')
                ->paginate(),
        ];
    }

    /**
     * Barra de comandos
     */
    public function commandBar(): array
    {
        return [
            Link::make('Voltar para Ativas')
                ->icon('bs.arrow-left')
                ->route('platform.propostas.index'),
        ];
    }

    /**
     * Layout da tabela
     */
    public function layout(): iterable
    {
        return [
            Layout::table('propostas', [
                TD::make('ordem', '#')
                    ->width('80px')
                    ->render(function (Proposta $p, $loop) {
                        $query = request()->route()->controller->query();
                        $pag = $query['propostas'] ?? null;
                        return $pag
                            ? $pag->firstItem() + $loop->index
                            : $loop->iteration;
                    }),
                TD::make('created_at', 'Data')
                    ->render(fn(Proposta $p) =>
                        $p->created_at?->format('d/m/Y') ?? '—'
                    ),
                TD::make('cliente', 'Cliente')
                    ->render(fn(Proposta $p) =>
                        $p->lead->nome ?? '—'
                    ),
                TD::make('valor_imovel', 'Valor do Imóvel')
                    ->render(fn(Proposta $p) =>
                        'R$ ' . number_format(
                            $p->valor_real ?? $p->valor_avaliacao ?? 0,
                            2, ',', '.'
                        )
                    ),
                TD::make('status', 'Status')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn(Proposta $p) =>
                        view('components.status-badge', ['status' => $p->status])
                    ),
                TD::make('Ações')
                    ->align(TD::ALIGN_CENTER)
                    ->width('160px')
                    ->render(fn(Proposta $p) =>
                        view('components.table-actions', [
                            'editRoute'  => route('platform.propostas.edit', $p),
                            'pdfRoute'   => route('platform.propostas.pdf', $p),
                            'viewRoute'  => route('platform.propostas.edit', $p), // Correção para $viewRoute
                            'archiveId'  => $p->id,
                            'isArchived' => true,
                        ])
                    ),
            ]),
        ];
    }
}