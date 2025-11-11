<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;
use App\Orchid\Screens\Propostas\PropostasKanbanScreen; // NOVA IMPORTAÇÃO

// --------------------------------------------------------------------------
// IMPORTAÇÃO DE TELAS (Screens)
// --------------------------------------------------------------------------

use App\Orchid\Screens\{
    // Core
    PlatformScreen,
    User\UserListScreen,
    User\UserEditScreen,
    User\UserProfileScreen,
    Role\RoleListScreen,
    Role\RoleEditScreen,

    // Módulos Customizados
    DashboardScreen,
    ClienteListScreen, ClienteEditScreen,
    ImovelListScreen, ImovelEditScreen,
    AluguelListScreen, AluguelEditScreen,
    ContratoListScreen, ContratoEditScreen,
    ComissaoListScreen, ComissaoEditScreen,
    PropostasListScreen, PropostasEditScreen, PropostaPdfScreen,
    LeadListScreen, LeadEditScreen, LeadKanbanScreen,
    ConstrutoraListScreen, ConstrutoraEditScreen,

    // Exemplos
    Examples\ExampleScreen,
    Examples\ExampleLayoutsScreen,
    Examples\ExampleFieldsScreen,
    Examples\ExampleFieldsAdvancedScreen,
    Examples\ExampleTextEditorsScreen,
    Examples\ExampleCardsScreen,
    Examples\ExampleChartsScreen,
    Examples\ExampleActionsScreen,
    Examples\ExampleGridScreen
};

// --------------------------------------------------------------------------
// DASHBOARD & PERFIL
// --------------------------------------------------------------------------
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

Route::screen('dashboard', DashboardScreen::class)
    ->name('platform.dashboard')
    ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.index')->push('Dashboard'));

Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.index')->push('Perfil'));

// --------------------------------------------------------------------------
// SISTEMA (USUÁRIOS & PAPÉIS)
// --------------------------------------------------------------------------
Route::prefix('systems')->name('platform.systems.')->group(function () {
    // --- Usuários ---
    Route::screen('users', UserListScreen::class)
        ->name('users')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.index')->push('Usuários'));

    Route::screen('users/create', UserEditScreen::class)
        ->name('users.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.systems.users')->push('Criar Usuário'));

    Route::screen('users/{user}/edit', UserEditScreen::class)
        ->name('users.edit')
        ->breadcrumbs(fn (Trail $trail, $user) => $trail->parent('platform.systems.users')->push($user->name));

    // --- Papéis (Roles) ---
    Route::screen('roles', RoleListScreen::class)
        ->name('roles')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.index')->push('Papéis e Permissões'));

    Route::screen('roles/create', RoleEditScreen::class)
        ->name('roles.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.systems.roles')->push('Criar Papel'));

    Route::screen('roles/{role}/edit', RoleEditScreen::class)
        ->name('roles.edit')
        ->breadcrumbs(fn (Trail $trail, $role) => $trail->parent('platform.systems.roles')->push($role->name));
});

// --------------------------------------------------------------------------
// MÓDULOS CRUD PADRÃO (construtoras, clientes, imóveis, etc)
// --------------------------------------------------------------------------
$crudModules = [
    'construtoras' => ConstrutoraListScreen::class,
    'clientes'     => ClienteListScreen::class,
    'imoveis'      => ImovelListScreen::class,
    'alugueis'     => AluguelListScreen::class,
    'contratos'    => ContratoListScreen::class,
    'comissoes'    => ComissaoListScreen::class,
];

foreach ($crudModules as $prefix => $listScreen) {
    // Assume a convenção de nomenclatura (ListScreen -> EditScreen)
    $editScreen = str_replace('List', 'Edit', $listScreen);

    Route::prefix($prefix)->name("platform.{$prefix}.")->group(function () use ($prefix, $listScreen, $editScreen) {
        
        Route::screen('/', $listScreen)
            ->name('index')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.index')->push(ucfirst($prefix)));

        Route::screen('create', $editScreen)
            ->name('create')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent("platform.{$prefix}.index")->push('Criar'));

        Route::screen('{model}/edit', $editScreen)
            ->name('edit')
            ->breadcrumbs(fn (Trail $trail, $model) => $trail->parent("platform.{$prefix}.index")->push(
                // Tenta encontrar o melhor "nome" para o breadcrumb
                $model->name ?? $model->titulo ?? $model->nome_razao_social ?? "Item #{$model->id}"
            ));
    });
}

// --------------------------------------------------------------------------
// PROPOSTAS (List, Create, Edit, PDF, KANBAN)
// --------------------------------------------------------------------------
Route::prefix('propostas')->name('platform.propostas.')->group(function () {

    // Listagem
    Route::screen('/', PropostasListScreen::class)
        ->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.index')->push('Propostas'));

    // Criação (usa a PropostasEditScreen com injeção de um novo modelo)
    Route::screen('create', PropostasEditScreen::class)
        ->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.propostas.index')->push('Criar Proposta'));

    // Criação a partir de um Lead (também usa PropostasEditScreen)
    Route::screen('create/{lead}', PropostasEditScreen::class)
        ->name('create.from.lead')
        ->breadcrumbs(fn (Trail $trail, $lead) => $trail->parent('platform.propostas.index')->push("Proposta para Lead #{$lead->id}"));

    // Edição
    Route::screen('{proposta}/edit', PropostasEditScreen::class)
        ->name('edit')
        ->breadcrumbs(fn (Trail $trail, $proposta) => $trail->parent('platform.propostas.index')->push("Proposta #{$proposta->id}"));

    // Visualização do PDF
    Route::screen('pdf/{proposta}', PropostaPdfScreen::class)
        ->name('pdf')
        ->breadcrumbs(fn (Trail $trail, $proposta) => $trail->parent('platform.propostas.index')->push("PDF Proposta #{$proposta->id}"));

    // *** ROTAS KANBAN INSERIDAS AQUI ***

    // Kanban de Propostas
    Route::screen('kanban', PropostasKanbanScreen::class)
        ->name('kanban')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.propostas.index')->push('Kanban'));

    // POST para atualização do status do Kanban
    Route::post('kanban/update', [PropostasKanbanScreen::class, 'updateStatus'])
        ->name('kanban.update');
});

// --------------------------------------------------------------------------
// LEADS (list, create, edit, kanban e update status)
// --------------------------------------------------------------------------
Route::prefix('leads')->name('platform.leads.')->group(function () {

    // Listagem
    Route::screen('/', LeadListScreen::class)
        ->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.index')->push('Leads'));

    // Criação
    Route::screen('create', LeadEditScreen::class)
        ->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.leads.index')->push('Criar Lead'));

    // Edição
    Route::screen('{lead}/edit', LeadEditScreen::class)
        ->name('edit')
        ->breadcrumbs(fn (Trail $trail, $lead) => $trail->parent('platform.leads.index')->push("Lead #{$lead->id}"));

    // Kanban
    Route::screen('kanban', LeadKanbanScreen::class)
        ->name('kanban')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('platform.leads.index')->push('Kanban'));

    // POST para atualização do status do Kanban
    Route::post('kanban/update', [LeadKanbanScreen::class, 'updateStatus'])
        ->name('kanban.update');
});


// --------------------------------------------------------------------------
// EXEMPLOS (em grupo com prefixo e nome)
// --------------------------------------------------------------------------
Route::prefix('examples')->name('platform.example.')->group(function () {
    Route::screen('form/fields', ExampleFieldsScreen::class)->name('fields');
    Route::screen('form/advanced', ExampleFieldsAdvancedScreen::class)->name('advanced');
    Route::screen('form/editors', ExampleTextEditorsScreen::class)->name('editors');
    Route::screen('form/actions', ExampleActionsScreen::class)->name('actions');
    Route::screen('layouts', ExampleLayoutsScreen::class)->name('layouts');
    Route::screen('grid', ExampleGridScreen::class)->name('grid');
    Route::screen('charts', ExampleChartsScreen::class)->name('charts');
    Route::screen('cards', ExampleCardsScreen::class)->name('cards');
    Route::screen('/', ExampleScreen::class)->name('index');




    // routes/platform.php
Route::post('propostas/calculate', [PropostasEditScreen::class, 'ajaxCalculate'])
    ->name('platform.propostas.calculate');

});