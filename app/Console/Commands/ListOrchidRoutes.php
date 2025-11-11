<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class ListOrchidRoutes extends Command
{
    protected $signature = 'orchid:routes';
    protected $description = 'Lista todas as rotas do Orchid e verifica a rota do menu Fluxo';

    public function handle(): int
    {
        try {
            $this->info('Limpando cache de rotas, configuração e view...');
            $this->callSilent('route:clear');
            $this->callSilent('config:clear');
            $this->callSilent('view:clear');

            $this->info('Recuperando e listando rotas do Orchid (prefixadas com platform.)...');

            $routes = Route::getRoutes()->getRoutesByName();
            $orchidRoutes = array_filter($routes, fn($key) => str_starts_with($key, 'platform.'), ARRAY_FILTER_USE_KEY);

            if (empty($orchidRoutes)) {
                $this->error('Nenhuma rota Orchid encontrada. Verifique se routes/platform.php está carregado corretamente.');
                Log::warning('Comando orchid:routes: Nenhuma rota Orchid detectada.', ['time' => now()]);
                return Command::FAILURE;
            }

            $tableData = array_map(function ($route) {
                return [
                    'name' => $route->getName(),
                    'uri' => $route->uri(),
                    'methods' => implode('|', $route->methods()),
                    'action' => $route->action['controller'] ?? 'Closure/' . ($route->action['uses'] ?? 'Unknown'),
                ];
            }, $orchidRoutes);

            $this->table(['Nome', 'URI', 'Método', 'Ação'], $tableData);

            $fluxoRouteExists = array_key_exists('platform.fluxo', $routes);
            if ($fluxoRouteExists) {
                $this->info('Rota platform.fluxo encontrada! O menu Fluxo deve ser exibido no sidebar se o usuário tiver permissão.');
                Log::info('Comando orchid:routes: Rota platform.fluxo confirmada.', ['time' => now()]);
            } else {
                $this->warn('Rota platform.fluxo NÃO encontrada. Adicione em routes/platform.php: Route::screen(\'fluxo\', FluxoScreen::class)->name(\'platform.fluxo\');');
                Log::warning('Comando orchid:routes: Rota platform.fluxo ausente.', ['time' => now()]);
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erro ao executar o comando: ' . $e->getMessage());
            Log::error('Comando orchid:routes falhou.', ['exception' => $e, 'time' => now()]);
            return Command::FAILURE;
        }
    }
}
