<?php
declare(strict_types=1);
namespace App\Orchid;
use Illuminate\Routing\Router;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use App\Orchid\Http\Middleware\Access;

/**
 * Provedor de serviços para configuração do painel Orchid.
 */
class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Registra serviços e middlewares do Orchid.
     */
    public function register(): void
    {
        parent::register();
        $this->app['router']->aliasMiddleware('access', Access::class);
    }

    /**
     * Inicializa o provedor e publica assets.
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);
        $this->publishAssets();
    }

    /**
     * Publica os assets JavaScript do Orchid.
     */
    private function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/../../resources/js' => resource_path('js'),
        ], 'orchid-js');
    }

    /**
     * Registra as rotas do painel a partir de routes/platform.php.
     */
    public function routes(Router $router): void
    {
        $platformRoutes = base_path('routes/platform.php');
        if (file_exists($platformRoutes)) {
            require $platformRoutes;
        }
    }

    /**
     * Configura os itens do menu do painel.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | PAINEL
            |--------------------------------------------------------------------------
            */
            Menu::make('Dashboard')
                ->icon('bs.house')
                ->route('platform.dashboard')
                ->title('Painel'),


            /*
            |--------------------------------------------------------------------------
            | SEÇÃO GERAL
            |--------------------------------------------------------------------------
            */
            Menu::make('Leads')
                ->icon('bs.magnet')
                ->route('platform.leads.index')
                ->title('Geral')
                ->list([
                    Menu::make('Kanban de Leads')
                        ->icon('bs.kanban')
                        ->route('platform.leads.kanban'),
                ]),

            Menu::make('Imóveis')
                ->icon('bs.building')
                ->route('platform.imoveis.index'),

            Menu::make('Construtoras')
                ->icon('bs.buildings')
                ->route('platform.construtoras.index'),


            /*
            |--------------------------------------------------------------------------
            | FINANCEIRO
            |--------------------------------------------------------------------------
            */
            Menu::make('Fluxo Financeiro')
                ->icon('bs.graph-up')
                ->route('platform.fluxo')
                ->title('Financeiro'),

            Menu::make('Propostas')
                ->icon('bs.file-earmark-text')
                ->route('platform.propostas.index')
                ->list([
                    Menu::make('Arquivadas')
                        ->icon('bs.archive')
                        ->route('platform.propostas.arquivadas'),
                ]),

            Menu::make('Contratos')
                ->icon('bs.file-contract')
                ->route('platform.contratos.index'),

            Menu::make('Comissões')
                ->icon('bs.currency-dollar')
                ->route('platform.comissoes.index'),


            /*
            |--------------------------------------------------------------------------
            | GESTÃO DE ALUGUÉIS
            |--------------------------------------------------------------------------
            */
            Menu::make('Aluguéis')
                ->icon('bs.house-heart')
                ->route('platform.alugueis.index')
                ->title('Gestão de Aluguéis'),

            Menu::make('Clientes')
                ->icon('bs.people')
                ->route('platform.clientes.index'),


            /*
            |--------------------------------------------------------------------------
            | SISTEMA
            |--------------------------------------------------------------------------
            */
            Menu::make('Perfil')
                ->icon('bs.person')
                ->route('platform.profile')
                ->title('Sistema'),

            Menu::make('Usuários')
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users'),

            Menu::make('Papéis')
                ->icon('bs.shield-lock')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),

            Menu::make('Configurações')
                ->icon('bs.gear')
                ->route('platform.configuracoes')
                ->list([
                    Menu::make('Prompts de IA')
                        ->icon('bs.robot')
                        ->route('platform.config.prompts.index'),
                ]),
        ];
    }

    /**
     * Configura as permissões do sistema.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('platform.systems'))
                ->addPermission('platform.systems.users', 'Acessar usuários'),
            ItemPermission::group(__('platform.config'))
                ->addPermission('platform.config.prompts', 'Gerenciar prompts IA'),
        ];
    }
}