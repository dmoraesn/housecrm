<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Proposta;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown; // <--- Correção: Usamos DropDown
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
     * Query das propostas ativas
     */
    public function query(): iterable
    {
        return [
            'propostas' => Proposta::with('lead')
                ->filters()
                ->where('status', '!=', 'arquivada')
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

                // --- CORREÇÃO AQUI ---
                // Substituímos Group (que não existe) por DropDown (padrão do Orchid)
                TD::make('Ações')
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
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
     * Arquiva proposta
     */
    public function archive(Request $request)
    {
        $proposta = Proposta::findOrFail($request->input('proposta'));
        $proposta->status = 'arquivada';
        $proposta->save();

        Alert::info("Proposta arquivada com sucesso!");
        return redirect()->route('platform.propostas');
    }

    /**
     * Desarquiva proposta
     */
    public function unarchive(Request $request)
    {
        $proposta = Proposta::findOrFail($request->input('proposta'));
        $proposta->status = 'ativo';
        $proposta->save();

        Alert::success("Proposta desarquivada com sucesso!");
        return redirect()->route('platform.propostas.arquivadas');
    }
}