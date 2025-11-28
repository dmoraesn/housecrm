<?php

namespace App\Providers;

use App\Models\GlobalSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Removido o Telescope (você não tem ele instalado)
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Carrega as configurações do banco em qualquer ambiente (web, queue, tinker, etc.)
        if (Schema::hasTable('global_settings')) {
            $this->loadGlobalSettings();
        }
    }

    /**
     * Carrega todas as chaves de API e configurações do banco para o config do Laravel
     */
    private function loadGlobalSettings(): void
    {
        // CORREÇÃO: Altera o namespace de 'services.openai.key' para 'openai.api_key'
        // para corresponder ao arquivo config/openai.php e ao esperado pela biblioteca.
        $openai_api_key = GlobalSetting::getValue('openai_api_key');
        if ($openai_api_key) {
            config()->set('openai.api_key', $openai_api_key);
        }

        // As chaves das outras APIs (gemini, anthropic, grok) provavelmente
        // também precisam ser ajustadas para seus respectivos arquivos de config.
        // Vamos presumir que você está usando 'services.gemini.key', etc., 
        // em outras partes do seu código, mas para o OpenAI o namespace é crítico aqui.
        config()->set('services.gemini.key', GlobalSetting::getValue('gemini_api_key'));
        config()->set('services.anthropic.key', GlobalSetting::getValue('anthropic_api_key'));
        config()->set('services.grok.key', GlobalSetting::getValue('grok_api_key'));

        // CORREÇÃO: Também ajusta o namespace do model, pois o arquivo é 'openai.php'.
        // O valor padrão ('gpt-4o-mini') está no código, não no DB, então 'config()->set' é o suficiente.
        config()->set('openai.model', GlobalSetting::getValue('openai_model', 'gpt-4o-mini'));
        
        // CORREÇÃO: Também ajusta o namespace da temperatura.
        config()->set('openai.temperature', (float) GlobalSetting::getValue('ai_temperature', '0.7'));
    }
}