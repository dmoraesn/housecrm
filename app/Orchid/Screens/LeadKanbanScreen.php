<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use App\Models\Lead;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;

class LeadKanbanScreen extends Screen
{
    /**
     * Nome e descrição
     */
    public $name = 'Leads';
    public $description = 'Funil de Vendas (Kanban)';

    /**
     * Consulta inicial
     */
    public function query(): array
    {
        $stages = [
            'novo'         => 'Novo Lead / Descoberta',
            'qualificacao' => 'Qualificação / Entendimento',
            'visita'       => 'Apresentação / Visita',
            'negociacao'   => 'Proposta / Negociação',
            'fechamento'   => 'Fechamento / Contrato',
        ];

        $leads = Lead::all()->groupBy('status');

        // Garante que todos os estágios existam, mesmo vazios
        foreach (array_keys($stages) as $key) {
            if (!isset($leads[$key])) {
                $leads[$key] = collect();
            }
        }

        return [
            'stages' => $stages,
            'leads'  => $leads,
        ];
    }

    /**
     * Barra de comandos superior
     */
    public function commandBar(): array
    {
        return [
            Link::make('Novo Lead')
                ->icon('plus')
                ->route('platform.leads.create'),
        ];
    }

    /**
     * Layout principal
     */
    public function layout(): array
    {
        return [
            Layout::view('platform::leads.kanban'),
        ];
    }

    /**
     * Atualiza o status de um lead via AJAX (drag & drop)
     */
    public function updateStatus(Request $request)
    {
        $lead = Lead::findOrFail($request->id);
        $lead->status = $request->status;
        $lead->save();

        return response()->json(['success' => true]);
    }
}
