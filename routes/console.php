<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('orchid:routes', function () {
    $this->call('App\Console\Commands\ListOrchidRoutes');
})->describe('Lista todas as rotas do Orchid e verifica a rota do menu Fluxo');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
