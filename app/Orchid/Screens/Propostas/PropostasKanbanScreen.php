<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Propostas;

use App\Models\Proposta;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;

class PropostasKanbanScreen extends Screen
{
    // Defina seus status aqui, se necessário
    private const STATUS_OPTIONS = [/* ... */]; 

    public function name(): string
    {
        return 'Kanban de Propostas';
    }

    public function query(): array
    {
        // Implemente a lógica de consulta e agrupamento das Propostas
        return [
            // Exemplo: 'propostas' => Proposta::orderBy('order')->get()->groupBy('status'),
            'statuses' => self::STATUS_OPTIONS,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Nova Proposta')->icon('bs.plus')->route('platform.propostas.create'),
        ];
    }

    public function layout(): iterable
    {
        // Aponta para o arquivo Blade que vamos criar abaixo
        return [
            Layout::view('platform.propostas.kanban'),
        ];
    }
    
    // Implemente o método updateStatus, se necessário
    // public function updateStatus(Request $request) { /* ... */ }
}