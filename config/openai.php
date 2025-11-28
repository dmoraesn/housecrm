<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    | Prioridade: banco de dados (GlobalSetting) → fallback .env
    */

    // CORREÇÃO: Usa uma closure para buscar o valor do DB em tempo de execução.
    'api_key' => function () {
        return \App\Models\GlobalSetting::getValue('openai_api_key') ?? env('OPENAI_API_KEY');
    },

    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Project
    |--------------------------------------------------------------------------
    */
    'project' => env('OPENAI_PROJECT'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Base URL
    |--------------------------------------------------------------------------
    */
    'base_uri' => env('OPENAI_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    */
    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 60),

];
