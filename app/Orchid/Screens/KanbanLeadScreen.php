<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use App\Models\Lead;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;

class KanbanLeadScreen extends Screen
{
    public $name = 'Leads';
    public $description = 'Funil de Vendas';

    public function query(): array
    {
        $stages = [
            'novo' => ['title' => 'Novo', 'color' => 'blue-500'],
            'em_andamento' => ['title' => 'Em andamento', 'color' => 'amber-500'],
            'convertido' => ['title' => 'Convertido', 'color' => 'green-500'],
            'perdido' => ['title' => 'Perdido', 'color' => 'red-500'],
        ];

        foreach ($stages as $key => &$stage) {
            $stage['leads'] = Lead::where('status', $key)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return ['stages' => $stages];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Novo Lead')
                ->icon('bs.plus-circle')
                ->route('platform.leads.create'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::view('orchid.leads.kanban'),
        ];
    }
}
