<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);
    }

    /**
     * Register the application menu.
     */
    public function menu(): array
    {
        return [
            // 🌐 DASHBOARD
            Menu::make('Dashboard')
                ->icon('bs.speedometer')
                ->route('platform.dashboard')
                ->title('Início'),

            // 📋 LEADS
            Menu::make('Leads')
                ->icon('bs.people')
                ->route('platform.leads')
                ->permission('platform.leads'),

            // 💸 COMISSÕES
            Menu::make('Comissões')
                ->icon('bs.currency-dollar')
                ->route('platform.comissoes')
                ->permission('platform.comissoes'),

            // 📑 PROPOSTAS
            Menu::make('Propostas')
                ->icon('bs.file-earmark-text')
                ->route('platform.propostas')
                ->permission('platform.propostas'),

            // 📜 CONTRATOS
            Menu::make('Contratos')
                ->icon('bs.file-earmark-check')
                ->route('platform.contratos')
                ->permission('platform.contratos'),

            // 🏠 ALUGUÉIS
            Menu::make('Aluguéis')
                ->icon('bs.house-door')
                ->route('platform.alugueis')
                ->permission('platform.alugueis'),

            // 🏗️ CONSTRUTORAS / PARCEIROS
            Menu::make('Construtoras / Parceiros')
                ->icon('bs.building')
                ->route('platform.construtoras')
                ->permission('platform.construtoras'),

            // 🏘️ IMÓVEIS
            Menu::make('Imóveis')
                ->icon('bs.buildings')
                ->route('platform.imoveis')
                ->permission('platform.imoveis'),

            // ⚙️ ADMINISTRAÇÃO (visível apenas para Admin)
            Menu::make(__('Usuários'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title('Administração'),

            Menu::make(__('Funções'))
                ->icon('bs.shield-lock')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),
        ];
    }

    /**
     * Register permissions for the application.
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group('Sistema')
                ->addPermission('platform.dashboard', 'Acessar Dashboard')
                ->addPermission('platform.leads', 'Gerenciar Leads')
                ->addPermission('platform.comissoes', 'Ver Comissões')
                ->addPermission('platform.propostas', 'Gerenciar Propostas')
                ->addPermission('platform.contratos', 'Gerenciar Contratos')
                ->addPermission('platform.alugueis', 'Gerenciar Aluguéis')
                ->addPermission('platform.construtoras', 'Ver Construtoras / Parceiros')
                ->addPermission('platform.imoveis', 'Gerenciar Imóveis')
                ->addPermission('platform.systems.users', 'Gerenciar Usuários')
                ->addPermission('platform.systems.roles', 'Gerenciar Funções'),
        ];
    }
}
