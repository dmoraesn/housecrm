<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Propostas;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use App\Models\Proposta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Support\Facades\Toast;

class PropostasKanbanScreen extends Screen
{
    /**
     * Status definidos no Kanban
     */
    private const STATUS = [
        'novo'      => 'Novo',
        'analise'   => 'Em Análise',
        'enviado'   => 'Enviado',
        'revisao'   => 'Revisão',
        'aceito'    => 'Aceito',
        'recusado'  => 'Recusado',
    ];

    public function name(): string
    {
        return 'Kanban de Propostas';
    }

    public function query(): array
    {
        return [
            'statuses' => self::STATUS,
            'propostas' => Proposta::orderBy('ordem')
                ->get()
                ->groupBy('status'),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Nova Proposta')
                ->icon('bs.plus')
                ->route('platform.propostas.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('platform.propostas.kanban'),
        ];
    }

    /**
     * Atualização via AJAX para SortableJS
     */
    public function updateStatus(Request $request)
    {
        $propostaId = $request->input('id');
        $status = $request->input('status');
        $ordem = $request->input('ordem'); // array

        if (!$propostaId || !$status) {
            return response()->json(['error' => 'Parâmetros inválidos'], 422);
        }

        DB::transaction(function () use ($propostaId, $status, $ordem) {

            // Atualiza o status da proposta movida
            Proposta::where('id', $propostaId)->update([
                'status' => $status,
            ]);

            // Atualiza a ordem das propostas dentro da coluna
            if (is_array($ordem)) {
                foreach ($ordem as $index => $id) {
                    Proposta::where('id', $id)->update(['ordem' => $index]);
                }
            }
        });

        return response()->json(['success' => true]);
    }
}
