<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use App\Orchid\Screens\PropostasEditScreen;
use Tabuna\Breadcrumbs\Trail;

// Rota padrão
Route::get('/', function () {
    return view('welcome');
});

// Agrupamento de rotas administrativas
Route::prefix('admin')->group(function () {
    // Rotas do Kanban de Leads
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::post('update-status', [LeadController::class, 'updateStatus'])
            ->name('updateStatus');
    });

    // Rotas de Propostas (ajustando para o contexto Orchid)
    Route::prefix('propostas')->name('platform.propostas.')->group(function () {
        Route::get('{proposta}/pdf', [PropostasEditScreen::class, 'generatePdf'])
            ->name('pdf')
            ->breadcrumbs(fn (Trail $trail, $proposta) =>
                $trail->parent('platform.propostas.index')->push("PDF da Proposta #{$proposta->id}")
            );
    });
});