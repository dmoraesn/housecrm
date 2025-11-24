<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Proposta;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PropostasListScreen extends Screen
{
    public $name = 'Propostas Ativas';
    public $description = 'Listagem e gerenciamento de propostas ativas.';

    /**
     * Status considerados ATIVOS
     */
    private const ACTIVE_STATUSES = [
        'rascunho',
        'novo',
        'analise',
        'enviado',
        'revisao',
        'aceito',
        'ativa',
    ];

    /**
     * Query das propostas
     */
    public function query(): iterable
    {
        return [
            'propostas' => Proposta::with('lead')
                ->filters()
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->orderBy('id', 'desc')
                ->paginate(),
        ];
    }

    /**
     * Barra de comandos
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Arquivadas')
                ->icon('bs.folder')
                ->route('platform.propostas.arquivadas'),

            Link::make('Nova Proposta')
                ->icon('bs.plus')
                ->route('platform.propostas.create'),
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
                        $pagination = request()->route()->controller->query()['propostas'] ?? null;
                        return $pagination
                            ? $pagination->firstItem() + $loop->index
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
                    ->alignCenter()
                    ->render(fn(Proposta $p) =>
                        view('components.status-badge', ['status' => $p->status])
                    ),

                TD::make('Ações')
                    ->alignCenter()
                    ->width('130px')
                    ->render(fn(Proposta $p) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make('Editar')
                                ->icon('bs.pencil')
                                ->route('platform.propostas.edit', $p),

                            Link::make('Visualizar')
                                ->icon('bs.eye')
                                ->route('platform.propostas.view', $p),

                            Button::make('Arquivar')
                                ->icon('bs.archive')
                                ->confirm('Deseja realmente arquivar esta proposta?')
                                ->method('archive', [
                                    'proposta' => $p->id,
                                ]),
                        ])),
            ]),
        ];
    }

    /**
     * Arquiva uma proposta
     */
    public function archive(Request $request)
    {
        $proposta = Proposta::findOrFail($request->input('proposta'));
        $proposta->status = 'arquivada';
        $proposta->save();

        Alert::info("Proposta arquivada com sucesso!");

        // CORREÇÃO AQUI
        return redirect()->route('platform.propostas.index');
    }

    /**
     * Desarquiva proposta
     */
    public function unarchive(Request $request)
    {
        $proposta = Proposta::findOrFail($request->input('proposta'));

        // status correto para retornar à listagem de ativos
        $proposta->status = 'ativa';
        $proposta->save();

        Alert::success("Proposta desarquivada com sucesso!");

        return redirect()->route('platform.propostas.arquivadas');
    }
}
