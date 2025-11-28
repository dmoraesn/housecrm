<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\GlobalSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; // Adicionado para verificar a tabela

class OpenAIServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Se a aplicação estiver sendo executada via console (CLI), o banco de dados
        // pode não estar acessível ou totalmente configurado durante o 'register'.
        // Preferimos executar a lógica no 'boot' para garantir que o DB esteja pronto.
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // CORREÇÃO ESSENCIAL: Verifica se a tabela existe ANTES de tentar ler
        // para evitar falhas de bootstrap em comandos Artisan (ex: cache, migrate).
        if (Schema::hasTable('global_settings')) {
            try {
                // Tenta buscar as configurações de API do GlobalSetting.
                $apiKey = GlobalSetting::getValue('openai_api_key');
                $organization = GlobalSetting::getValue('openai_organization');
                $project = GlobalSetting::getValue('openai_project');
                $model = GlobalSetting::getValue('openai_model');
                $temperature = GlobalSetting::getValue('ai_temperature');

                // Apenas se a chave for encontrada no DB, sobrescreve o config/openai.php.
                if (! empty($apiKey)) {
                    $this->app['config']->set('openai.api_key', $apiKey);
                }
                
                if (! empty($organization)) {
                    $this->app['config']->set('openai.organization', $organization);
                }
                
                if (! empty($project)) {
                    $this->app['config']->set('openai.project', $project);
                }

                // INJEÇÃO FALTANTE: Injeta o modelo e a temperatura se encontrados no DB
                // para garantir que o LeadAiController use os valores dinâmicos.
                if (! empty($model)) {
                    $this->app['config']->set('openai.model', $model);
                }

                if (! empty($temperature)) {
                    // Garante que a temperatura seja float, como esperado no Controller
                    $this->app['config']->set('openai.temperature', (float) $temperature);
                }

            } catch (\Throwable $e) {
                // Ignora exceções de DB, mas a verificação Schema::hasTable já deve prevenir a maioria.
                // Log::warning('Falha ao carregar configurações da OpenAI do DB: ' . $e->getMessage());
            }
        }
    }
}