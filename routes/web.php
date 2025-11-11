<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;

Route::get('/', function () {
    return view('welcome');
});

// ==============================================
// ROTAS DO KANBAN DE LEADS
// ==============================================
Route::post('/admin/leads/update-status', [LeadController::class, 'updateStatus'])
    ->name('leads.updateStatus');


    