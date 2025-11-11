<?php
declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Lead;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function query(): array
    {
        $leads = Lead::with(['corretor', 'propostas', 'contratos'])
            ->orderBy('order')
            ->get()
            ->groupBy('status');

        return [
            'leads'    => $leads,
            'statuses' => self::STATUS_OPTIONS,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Novo Lead')
                ->icon('bs.plus')
                ->route('platform.leads.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('platform.leads.kanban'),
        ];
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|integer|exists:leads,id',
            'status' => 'required|string|in:' . implode(',', array_keys(self::STATUS_OPTIONS)),
            'order'  => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $lead = Lead::findOrFail($request->id);
                $oldStatus = $lead->status;
                $oldOrder = $lead->order;
                $newStatus = $request->status;
                $newOrder = $request->order;

                if ($oldStatus === $newStatus && $oldOrder === $newOrder) {
                    return;
                }

                if ($oldStatus === $newStatus) {
                    if ($newOrder > $oldOrder) {
                        Lead::where('status', $oldStatus)
                            ->whereBetween('order', [$oldOrder + 1, $newOrder])
                            ->decrement('order');
                    } else {
                        Lead::where('status', $oldStatus)
                            ->whereBetween('order', [$newOrder, $oldOrder - 1])
                            ->increment('order');
                    }
                } else {
                    Lead::where('status', $oldStatus)
                        ->where('order', '>', $oldOrder)
                        ->decrement('order');
                    Lead::where('status', $newStatus)
                        ->where('order', '>=', $newOrder)
                        ->increment('order');
                }

                $lead->status = $newStatus;
                $lead->order = $newOrder;
                $lead->save();
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}