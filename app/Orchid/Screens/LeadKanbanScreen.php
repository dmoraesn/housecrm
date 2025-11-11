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
    public function updateKanban(UpdateKanbanRequest $request) // <-- Usando o Form Request
    {
        // 1. Os dados já foram validados pelo Form Request
        $data = $request->validated();

        $leadId = $data['id'];
        $newStatus = $data['status'];
        $columnOrder = $data['column_order'];

        try {
            DB::transaction(function () use ($leadId, $newStatus, $columnOrder) {

                // A. Atualizar o status e a ordem do lead que foi arrastado
                $lead = Lead::findOrFail($leadId);
                $lead->status = $newStatus;

                // Procuramos a nova ordem dentro da lista enviada pelo frontend
                $newOrder = collect($columnOrder)->firstWhere('id', $leadId)['order'];
                $lead->order = $newOrder;
                $lead->save();

                // B. Aplicar a ordem para TODOS os leads na nova coluna
                foreach ($columnOrder as $item) {
                    Lead::where('id', $item['id'])->update(['order' => $item['order']]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Lead #{$leadId} movido para '{$newStatus}' e reordenado."
            ]);

        } catch (\Throwable $e) {
            // Em caso de qualquer erro, retorna 500 para o frontend
            return response()->json([
                'success' => false,
                'message' => 'Falha na atualização do Kanban: ' . $e->getMessage(),
            ], 500);
        }
    }
}
