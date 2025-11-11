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
        return array_map(
            fn (array $item): Menu => $this->buildMenuItem($item),
            OrchidConfig::getMenuItems()
        );
    }

    /**
     * Configura as permissões do sistema.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return array_map(
            fn (array $group): ItemPermission => $this->buildPermissionGroup($group),
            OrchidConfig::getPermissions()
        );
    }

    /**
     * Constrói um item de menu a partir da configuração.
     */
    private function buildMenuItem(array $item): Menu
    {
        $menu = Menu::make($item['name'])
            ->icon($item['icon'])
            ->route($item['route']);

        if (isset($item['permission'])) {
            $menu->permission($item['permission']);
        }

        if (isset($item['title'])) {
            $menu->title($item['title']);
        }

        return $menu;
    }

    /**
     * Constrói um grupo de permissões a partir da configuração.
     */
    private function buildPermissionGroup(array $group): ItemPermission
    {
        $permission = ItemPermission::group($group['group']);

        foreach ($group['items'] as $key => $description) {
            $permission->addPermission($key, $description);
        }

        return $permission;
    }
}
