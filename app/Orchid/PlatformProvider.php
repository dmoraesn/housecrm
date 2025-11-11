<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Facades\Dashboard as OrchidDashboard;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ------------------------------------------------------------------
        // Recursos globais (CSS/JS base)
        // ------------------------------------------------------------------
        OrchidDashboard::registerResource('stylesheets', [
            asset('vendor/orchid/css/orchid.css'),
        ]);

        // ------------------------------------------------------------------
        // Recursos nomeados (Kanban)
        // ------------------------------------------------------------------
        OrchidDashboard::registerResource('stylesheets', 'kanban', asset('css/kanban.css'));
        OrchidDashboard::registerResource('scripts', 'kanban-js', asset('js/kanban.js'));
    }

    /**
     * Menu lateral do painel.
     */
    public function menu(): array
    {
        return [
            // === INÍCIO ===
            Menu::make('Dashboard')
                ->icon('bs.speedometer')
                ->route('platform.dashboard')
                ->title('Início'),

            // === LEADS ===
            Menu::make('Leads')
                ->icon('bs.people')
                ->route('platform.leads.index')
                ->permission('platform.leads'),

            Menu::make('Kanban de Leads')
                ->icon('bs.layout-three-columns')
                ->route('platform.leads.kanban')
                ->permission('platform.leads'),

            // === PROPOSTAS ===
            Menu::make('Propostas')
                ->icon('bs.file-earmark-text')
                ->route('platform.propostas.index')
                ->permission('platform.propostas'),

            // === COMISSÕES ===
            Menu::make('Comissões')
                ->icon('bs.currency-dollar')
                ->route('platform.comissoes.index')
                ->permission('platform.comissoes'),

            // === CONTRATOS ===
            Menu::make('Contratos')
                ->icon('bs.file-earmark-check')
                ->route('platform.contratos.index')
                ->permission('platform.contratos'),

            // === CONSTRUTORAS / PARCEIROS ===
            Menu::make('Construtoras / Parceiros')
                ->icon('bs.building')
                ->route('platform.construtoras.index')
                ->permission('platform.construtoras'),

            // === IMÓVEIS ===
            Menu::make('Imóveis')
                ->icon('bs.buildings')
                ->route('platform.imoveis.index')
                ->permission('platform.imoveis'),

            // === ALUGUÉIS ===
            Menu::make('Aluguéis')
                ->icon('bs.key')
                ->route('platform.alugueis.index')
                ->permission('platform.alugueis'),

            // === ADMINISTRAÇÃO ===
            Menu::make('Usuários')
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title('Administração'),

            Menu::make('Funções')
                ->icon('bs.shield-lock')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),
        ];
    }

    /**
     * Permissões do sistema.
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group('Sistema')
                ->addPermission('platform.dashboard', 'Acessar Dashboard')
                ->addPermission('platform.leads', 'Gerenciar Leads')
                ->addPermission('platform.propostas', 'Gerenciar Propostas')
                ->addPermission('platform.comissoes', 'Gerenciar Comissões')
                ->addPermission('platform.contratos', 'Gerenciar Contratos')
                ->addPermission('platform.alugueis', 'Gerenciar Aluguéis')
                ->addPermission('platform.construtoras', 'Gerenciar Construtoras')
                ->addPermission('platform.imoveis', 'Gerenciar Imóveis')
                ->addPermission('platform.systems.users', 'Gerenciar Usuários')
                ->addPermission('platform.systems.roles', 'Gerenciar Funções'),
        ];
    }
}