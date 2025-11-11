<?php

namespace App\Orchid\Screens;

use App\Models\Lead;
use App\Models\User;
use App\Models\Aluguel;
use App\Models\Imovel;
use App\Models\Proposta;
use App\Models\Contrato;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Facades\Schema;

class DashboardScreen extends Screen
{
    public function name(): string
    {
        return 'Dashboard';
    }

    public function description(): ?string
    {
        return 'Visão geral do sistema de leads e vendas.';
    }

    public function query(): array
    {
        $vgv = 0;
        if (Schema::hasColumn('propostas', 'valor_total')) {
            $vgv += Proposta::where('status', 'fechado')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('valor_total');
        }
        if (Schema::hasColumn('contratos', 'valor')) {
            $vgv += Contrato::where('status', 'ativo')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('valor');
        }

        return [
            'vgv_mes' => 'R$ ' . number_format($vgv, 2, ',', '.'),
            'corretores_time' => User::whereHas('roles', fn($q) => $q->where('slug', 'corretor'))->count(),
            'leads_totais' => Lead::count(),
            'alugueis_ativos' => class_exists(Aluguel::class) && Schema::hasTable('alugueis')
                ? Aluguel::where('status', 'ativo')->count()
                : 0,
            'imoveis_cadastrados' => class_exists(Imovel::class) && Schema::hasTable('imoveis')
                ? Imovel::count()
                : 0,
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Novo Lead')
                ->route('platform.leads.create')
                ->icon('bs.plus-circle'),

            Link::make('Kanban')
                ->route('platform.leads.kanban')
                ->icon('bs.grid-3x3-gap'),
        ];
    }

    public function layout(): array
    {
        return [
            // Cards customizados (sem uso de legend/metric)
            Layout::view('platform.dashboard-cards'),

            // Dashboard padrão do Orchid (opcional, para conteúdo extra)
            Layout::view('platform::dashboard'),
        ];
    }
}