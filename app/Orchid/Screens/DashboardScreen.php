<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use App\Models\Lead; // ✅ Importação essencial

class DashboardScreen extends Screen
{
    public $name = 'Dashboard - Leads';
    public $description = 'Resumo visual de Leads';

    public function query(): array
    {
        $leads = Lead::all()->groupBy('status');
        return ['leads' => $leads];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Novo Lead')->icon('plus')->route('platform.leads.create'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::view('platform::partials.leads-kanban'),
        ];
    }
}
