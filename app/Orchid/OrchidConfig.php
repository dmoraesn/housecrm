<?php

declare(strict_types=1);

namespace App\Orchid;

/**
 * Configurações estáticas para o painel Orchid, incluindo menus e permissões.
 */
final class OrchidConfig
{
    /**
     * Itens do menu do painel.
     *
     * @return array<array{name: string, icon: string, route: string, permission?: string, title?: string}>
     */
    public static function getMenuItems(): array
    {
        return [
            [
                'name' => 'Dashboard',
                'icon' => 'bs.speedometer',
                'route' => 'platform.dashboard',
                'title' => 'Início',
            ],
            [
                'name' => 'Leads',
                'icon' => 'bs.people',
                'route' => 'platform.leads.index',
                'permission' => 'platform.leads',
                'title' => 'Gestão de Vendas',
            ],
            [
                'name' => 'Kanban de Leads',
                'icon' => 'bs.layout-three-columns',
                'route' => 'platform.leads.kanban',
                'permission' => 'platform.leads',
            ],
            [
                'name' => 'Fluxo',
                'icon' => 'bs.arrow-right-circle',
                'route' => 'platform.fluxo',
                'permission' => 'platform.fluxo',
            ],
            [
                'name' => 'Propostas',
                'icon' => 'bs.file-earmark-text',
                'route' => 'platform.propostas.index',
                'permission' => 'platform.propostas',
            ],
            [
                'name' => 'Comissões',
                'icon' => 'bs.currency-dollar',
                'route' => 'platform.comissoes.index',
                'permission' => 'platform.comissoes',
            ],
            [
                'name' => 'Contratos',
                'icon' => 'bs.file-earmark-check',
                'route' => 'platform.contratos.index',
                'permission' => 'platform.contratos',
            ],
            [
                'name' => 'Construtoras / Parceiros',
                'icon' => 'bs.building',
                'route' => 'platform.construtoras.index',
                'permission' => 'platform.construtoras',
                'title' => 'Parceiros e Imóveis',
            ],
            [
                'name' => 'Imóveis',
                'icon' => 'bs.buildings',
                'route' => 'platform.imoveis.index',
                'permission' => 'platform.imoveis',
            ],
            [
                'name' => 'Aluguéis',
                'icon' => 'bs.key',
                'route' => 'platform.alugueis.index',
                'permission' => 'platform.alugueis',
            ],
            [
                'name' => 'Usuários',
                'icon' => 'bs.people',
                'route' => 'platform.systems.users',
                'permission' => 'platform.systems.users',
                'title' => 'Administração',
            ],
            [
                'name' => 'Funções',
                'icon' => 'bs.shield-lock',
                'route' => 'platform.systems.roles',
                'permission' => 'platform.systems.roles',
            ],
        ];
    }

    /**
     * Grupos de permissões do sistema.
     *
     * @return array<array{group: string, items: array<string, string>}>
     */
    public static function getPermissions(): array
    {
        return [
            [
                'group' => 'Sistema',
                'items' => [
                    'platform.dashboard' => 'Acessar Dashboard',
                    'platform.leads' => 'Gerenciar Leads',
                    'platform.fluxo' => 'Gerenciar Fluxo de Vendas',
                    'platform.propostas' => 'Gerenciar Propostas',
                    'platform.comissoes' => 'Gerenciar Comissões',
                    'platform.contratos' => 'Gerenciar Contratos',
                    'platform.alugueis' => 'Gerenciar Aluguéis',
                    'platform.construtoras' => 'Gerenciar Construtoras',
                    'platform.imoveis' => 'Gerenciar Imóveis',
                    'platform.systems.users' => 'Gerenciar Usuários',
                    'platform.systems.roles' => 'Gerenciar Funções',
                ],
            ],
        ];
    }
}
