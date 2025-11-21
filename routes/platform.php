<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

// --------------------------------------------------------------------------
// MODELOS
// --------------------------------------------------------------------------
use App\Models\{
    Construtora,
    Cliente,
    Imovel,
    Aluguel,
    Contrato,
    Comissao,
    Proposta,
    Lead
};

// --------------------------------------------------------------------------
// SCREENS
// --------------------------------------------------------------------------
use App\Orchid\Screens\{
    PlatformScreen,
    DashboardScreen,
    // Users & Roles
    User\UserListScreen,
    User\UserEditScreen,
    User\UserProfileScreen,
    Role\RoleListScreen,
    Role\RoleEditScreen,
    // CRUD
    ClienteListScreen, ClienteEditScreen,
    ImovelListScreen, ImovelEditScreen,
    AluguelListScreen, AluguelEditScreen,
    ContratoListScreen, ContratoEditScreen,
    ComissaoListScreen, ComissaoEditScreen,
    // Construtora
    ConstrutoraListScreen,
    ConstrutoraEditScreen,
    ConstrutoraCreateAutoScreen,
    ConstrutoraCreateManualScreen,
    // Leads
    LeadListScreen, LeadEditScreen, LeadKanbanScreen,
    // Propostas
    PropostasListScreen,
    PropostasEditScreen,
    PropostasArquivadasScreen
};
// Kanban
use App\Orchid\Screens\Propostas\PropostasKanbanScreen;
// Fluxo
use App\Orchid\Screens\FluxoScreen;
// Exemplos
use App\Orchid\Screens\Examples\{
    ExampleScreen,
    ExampleLayoutsScreen,
    ExampleFieldsScreen,
    ExampleFieldsAdvancedScreen,
    ExampleTextEditorsScreen,
    ExampleCardsScreen,
    ExampleChartsScreen,
    ExampleActionsScreen,
    ExampleGridScreen
};

// --------------------------------------------------------------------------
// DASHBOARD
// --------------------------------------------------------------------------
Route::screen('/main', PlatformScreen::class)->name('platform.main');
Route::screen('dashboard', DashboardScreen::class)
    ->name('platform.dashboard')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Dashboard')
    );
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Perfil')
    );
Route::screen('fluxo', FluxoScreen::class)
    ->name('platform.fluxo');

// --------------------------------------------------------------------------
// USERS & ROLES
// --------------------------------------------------------------------------
Route::prefix('systems')->name('platform.systems.')->group(function () {
    Route::screen('users', UserListScreen::class)
        ->name('users')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.index')->push('Usuários')
        );
    Route::screen('users/create', UserEditScreen::class)
        ->name('users.create')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.systems.users')->push('Criar Usuário')
        );
    Route::screen('users/{user}/edit', UserEditScreen::class)
        ->name('users.edit')
        ->breadcrumbs(fn (Trail $trail, $user) =>
            $trail->parent('platform.systems.users')->push($user->name)
        );
    Route::screen('roles', RoleListScreen::class)
        ->name('roles')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.index')->push('Papéis e Permissões')
        );
    Route::screen('roles/create', RoleEditScreen::class)
        ->name('roles.create')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.systems.roles')->push('Criar Papel')
        );
    Route::screen('roles/{role}/edit', RoleEditScreen::class)
        ->name('roles.edit')
        ->breadcrumbs(fn (Trail $trail, $role) =>
            $trail->parent('platform.systems.roles')->push($role->name)
        );
});

// --------------------------------------------------------------------------
// CRUDs GENÉRICOS
// --------------------------------------------------------------------------
$crudModules = [
    'clientes' => [
        'list' => ClienteListScreen::class,
        'edit' => ClienteEditScreen::class,
        'model' => Cliente::class,
    ],
    'imoveis' => [
        'list' => ImovelListScreen::class,
        'edit' => ImovelEditScreen::class,
        'model' => Imovel::class,
    ],
    'alugueis' => [
        'list' => AluguelListScreen::class,
        'edit' => AluguelEditScreen::class,
        'model' => Aluguel::class,
    ],
    'contratos' => [
        'list' => ContratoListScreen::class,
        'edit' => ContratoEditScreen::class,
        'model' => Contrato::class,
    ],
    'comissoes' => [
        'list' => ComissaoListScreen::class,
        'edit' => ComissaoEditScreen::class,
        'model' => Comissao::class,
    ],
];
foreach ($crudModules as $prefix => $module) {
    Route::prefix($prefix)->name("platform.{$prefix}.")->group(function () use ($prefix, $module) {
        // LIST
        Route::screen('/', $module['list'])
            ->name('index')
            ->breadcrumbs(fn (Trail $trail) =>
                $trail->parent('platform.index')->push(ucfirst($prefix))
            );
        // CREATE
        Route::screen('create', $module['edit'])
            ->name('create')
            ->breadcrumbs(fn (Trail $trail) =>
                $trail->parent("platform.{$prefix}.index")->push('Criar')
            );
        // EDIT
        Route::screen("{{$prefix}}/edit", $module['edit'])
            ->name('edit')
            ->breadcrumbs(function (Trail $trail, $model) use ($prefix, $module) {
                $modelInstance = is_string($model)
                    ? $module['model']::findOrFail($model)
                    : $model;
                return $trail->parent("platform.{$prefix}.index")
                    ->push(
                        $modelInstance->name
                        ?? $modelInstance->titulo
                        ?? $modelInstance->nome_razao_social
                        ?? "#{$modelInstance->id}"
                    );
            });
    });
}

// --------------------------------------------------------------------------
// CONSTRUTORAS
// --------------------------------------------------------------------------
Route::prefix('construtoras')->name("platform.construtoras.")->group(function () {
    Route::screen('/', ConstrutoraListScreen::class)
        ->name('index')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.index')->push('Construtoras')
        );
    Route::screen('create-auto', ConstrutoraCreateAutoScreen::class)
        ->name('create.auto')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent("platform.construtoras.index")->push('Novo (Automático)')
        );
    Route::screen('create-manual', ConstrutoraCreateManualScreen::class)
        ->name('create.manual')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent("platform.construtoras.index")->push('Novo (Manual)')
        );
    Route::screen('{construtora}/edit', ConstrutoraEditScreen::class)
        ->name('edit')
        ->whereNumber('construtora') // Correção: Garante que é ID
        ->breadcrumbs(function (Trail $trail, Construtora $construtora) {
            return $trail->parent("platform.construtoras.index")
                ->push(
                    $construtora->name
                    ?? $construtora->titulo
                    ?? $construtora->nome_razao_social
                    ?? "#{$construtora->id}"
                );
        });
});

// --------------------------------------------------------------------------
// PROPOSTAS
// --------------------------------------------------------------------------
Route::prefix('propostas')->name('platform.propostas.')->group(function () {
    // 1. Rotas Estáticas (Sempre devem vir antes das dinâmicas)
    Route::screen('/', PropostasListScreen::class)
        ->name('index')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.index')->push('Propostas')
        );

    Route::screen('create', PropostasEditScreen::class)
        ->name('create')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.propostas.index')->push('Criar Proposta')
        );

    Route::screen('arquivadas', PropostasArquivadasScreen::class)
        ->name('arquivadas');

    Route::screen('kanban', PropostasKanbanScreen::class)
        ->name('kanban');
        
    Route::post('kanban/update', [PropostasKanbanScreen::class, 'updateStatus'])
        ->name('kanban.update');

    Route::post('archive', [PropostasListScreen::class, 'archive'])
        ->name('archive');

    Route::post('unarchive', [PropostasListScreen::class, 'unarchive'])
        ->name('unarchive');
    
    // Calculadora via AJAX (POST)
    Route::post('calculate', [PropostasEditScreen::class, 'ajaxCalculate'])
        ->name('calculate');

    // 2. Rotas com Parâmetros (IDs)
    Route::screen('create/{lead}', PropostasEditScreen::class)
        ->name('create.from.lead')
        ->breadcrumbs(fn (Trail $trail, Lead $lead) =>
            $trail->parent('platform.propostas.index')
                ->push("Proposta para Lead #{$lead->id}")
        );

    Route::get('{proposta}/delete', [PropostasListScreen::class, 'remove'])
        ->name('delete')
        ->whereNumber('proposta');

    Route::get('{proposta}/pdf', [PropostasEditScreen::class, 'generatePdf'])
        ->name('pdf')
        ->whereNumber('proposta')
        ->breadcrumbs(fn (Trail $trail, Proposta $proposta) =>
            $trail->parent('platform.propostas.index')->push("PDF da Proposta #{$proposta->id}")
        );

    // Edit Screen
    Route::screen('{proposta}/edit', PropostasEditScreen::class)
        ->name('edit')
        ->whereNumber('proposta'); // Correção: Garante que é número

    // View Screen (A problemática)
    Route::screen('{proposta}/view', PropostasEditScreen::class)
        ->name('view')
        ->whereNumber('proposta') // Correção: Garante que é número e evita conflito com strings
        ->breadcrumbs(fn (Trail $trail, Proposta $proposta) =>
            $trail->parent('platform.propostas.index')->push("Visualizar Proposta #{$proposta->id}")
        );
});

// --------------------------------------------------------------------------
// LEADS
// --------------------------------------------------------------------------
Route::prefix('leads')->name('platform.leads.')->group(function () {
    Route::screen('/', LeadListScreen::class)
        ->name('index')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.index')->push('Leads')
        );
    Route::screen('create', LeadEditScreen::class)
        ->name('create');
    
    Route::screen('kanban', LeadKanbanScreen::class)
        ->name('kanban');
    Route::post('kanban/update', [LeadKanbanScreen::class, 'updateKanban'])
        ->name('kanban.update');
    Route::post('kanban/update-drag', [LeadKanbanScreen::class, 'updateKanban'])
        ->name('kanban.update.drag');

    Route::screen('{lead}/edit', LeadEditScreen::class)
        ->name('edit')
        ->whereNumber('lead');
});

// --------------------------------------------------------------------------
// EXEMPLOS
// --------------------------------------------------------------------------
Route::prefix('examples')->name('platform.example.')->group(function () {
    Route::screen('/', ExampleScreen::class)->name('index');
    Route::screen('form/fields', ExampleFieldsScreen::class)->name('fields');
    Route::screen('form/advanced', ExampleFieldsAdvancedScreen::class)->name('advanced');
    Route::screen('form/editors', ExampleTextEditorsScreen::class)->name('editors');
    Route::screen('form/actions', ExampleActionsScreen::class)->name('actions');
    Route::screen('layouts', ExampleLayoutsScreen::class)->name('layouts');
    Route::screen('grid', ExampleGridScreen::class)->name('grid');
    Route::screen('charts', ExampleChartsScreen::class)->name('charts');
    Route::screen('cards', ExampleCardsScreen::class)->name('cards');
});