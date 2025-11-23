<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Lead;
use Illuminate\Http\Request; // Ainda necessário para o método updateKanban
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
// use Illuminate\Support\Facades\Validator; // Removido
// use Illuminate\Validation\Rule; // Removido
use App\Http\Requests\UpdateKanbanRequest; // <-- NOVO

class LeadKanbanScreen extends Screen
{
    private const STATUS_OPTIONS = [
        'novo'         => 'Novo',
        'qualificacao' => 'Qualificação',
        'visita'       => 'Visita',
        'negociacao'   => 'Negociação',
        'fechamento'   => 'Fechamento',
        'perdido'      => 'Perdido',
    ];

    public function name(): string
    {
        return 'Kanban de Leads';
    }

    public function description(): string
    {
        return 'Visualize e organize os leads por etapa do funil.';
    }

    public function permission(): ?iterable
    {
        return ['platform.leads'];
    }

    /**
     * @return array
     */
    public function query(): array
    {
        // 1. Otimização: Agrupa os leads pelo status
        $leads = Lead::with(['corretor', 'propostas', 'contratos'])
            ->orderBy('order')
            ->get()
            ->groupBy('status');

        // 2. Mapeia os status definidos para a estrutura de colunas do Kanban
        $columns = collect(self::STATUS_OPTIONS)->map(function ($label, $status) use ($leads) {
            return (object) [
                'name'   => $label,
                'status' => $status,
                'leads'  => $leads->get($status, collect()),
            ];
        });

        return compact('columns');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Novo Lead')
                ->icon('bs.plus')
                ->route('platform.leads.create')
                ->canSee(true), // Exemplo de permissão
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('platform.leads.kanban'),
        ];
    }

    /**
     * Lida com a requisição POST para atualizar o status e a ordem do Kanban.
     * Usa UpdateKanbanRequest para garantir que a resposta de falha seja JSON.
     *
     * @param UpdateKanbanRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
 public function updateKanban(UpdateKanbanRequest $request)
{
    $leadId = $request->id;
    $newStatus = $request->status;
    $newOrder = $request->order;

    try {
        DB::transaction(function () use ($leadId, $newStatus, $newOrder) {

            // Atualiza o lead movido
            $lead = Lead::findOrFail($leadId);
            $lead->status = $newStatus;
            $lead->order = $newOrder;
            $lead->save();

            // Reordena todos os leads da coluna
            $leads = Lead::where('status', $newStatus)
                ->where('id', '!=', $leadId)
                ->orderBy('order')
                ->get();

            $order = 0;

            foreach ($leads as $l) {
                if ($order == $newOrder) {
                    $order++;
                }
                $l->order = $order++;
                $l->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Lead $leadId movido para $newStatus"
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => "Erro ao atualizar Kanban: " . $e->getMessage()
        ], 500);
    }
}

}
