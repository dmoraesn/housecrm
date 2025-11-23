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
// SCREENS GERAIS
// --------------------------------------------------------------------------
use App\Orchid\Screens\{
    PlatformScreen,
    DashboardScreen,
    FluxoScreen,
    // Usuários e Perfis
    User\UserListScreen,
    User\UserEditScreen,
    User\UserProfileScreen,
    Role\RoleListScreen,
    Role\RoleEditScreen,
    // CRUDs genéricos
    ClienteListScreen,
    ClienteEditScreen,
    ImovelListScreen,
    ImovelEditScreen,
    AluguelListScreen,
    AluguelEditScreen,
    ContratoListScreen,
    ContratoEditScreen,
    ComissaoListScreen,
    ComissaoEditScreen,
    // Construtoras
    ConstrutoraListScreen,
    ConstrutoraEditScreen,
    ConstrutoraCreateAutoScreen,
    ConstrutoraCreateManualScreen,
    // Leads
    LeadListScreen,
    LeadEditScreen,
    LeadKanbanScreen,
    // Propostas (nível base)
    PropostasListScreen,
    PropostasArquivadasScreen,
    // Configurações
    Configuracao\ImobiliariaConfigScreen
};
// --------------------------------------------------------------------------
// PROPOSTAS (namespace correto)
// --------------------------------------------------------------------------
use App\Orchid\Screens\Propostas\{
    PropostasEditScreen,
    PropostasKanbanScreen
};
// --------------------------------------------------------------------------
// ADIÇÃO IA: Import para o controlador de IA
use App\Http\Controllers\Platform\LeadAiController;
// --------------------------------------------------------------------------
// EXEMPLOS DO ORCHID
// --------------------------------------------------------------------------
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
// DASHBOARD / PERFIL / FLUXO
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
    ->name('platform.fluxo')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Fluxo Financeiro')
    );
// --------------------------------------------------------------------------
// USERS & ROLES
// --------------------------------------------------------------------------
Route::prefix('systems')->name('platform.systems.')->group(function () {
    // USERS
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
            $trail->parent('platform.systems.users')->push($user->name ?? "Usuário #{$user->id}")
        );
    // ROLES
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
            $trail->parent('platform.systems.roles')->push($role->name ?? "Papel #{$role->id}")
        );
});
// --------------------------------------------------------------------------
// CRDs GENÉRICOS (Clientes, Imóveis, Aluguéis, Contratos, Comissões)
// --------------------------------------------------------------------------
$crudModules = [
    'clientes' => [
        'list'  => ClienteListScreen::class,
        'edit'  => ClienteEditScreen::class,
        'model' => Cliente::class,
        'label' => 'Clientes',
    ],
    'imoveis' => [
        'list'  => ImovelListScreen::class,
        'edit'  => ImovelEditScreen::class,
        'model' => Imovel::class,
        'label' => 'Imóveis',
    ],
    'alugueis' => [
        'list'  => AluguelListScreen::class,
        'edit'  => AluguelEditScreen::class,
        'model' => Aluguel::class,
        'label' => 'Aluguéis',
    ],
    'contratos' => [
        'list'  => ContratoListScreen::class,
        'edit'  => ContratoEditScreen::class,
        'model' => Contrato::class,
        'label' => 'Contratos',
    ],
    'comissoes' => [
        'list'  => ComissaoListScreen::class,
        'edit'  => ComissaoEditScreen::class,
        'model' => Comissao::class,
        'label' => 'Comissões',
    ],
];
foreach ($crudModules as $prefix => $module) {
    Route::prefix($prefix)->name("platform.{$prefix}.")->group(function () use ($prefix, $module) {
        Route::screen('/', $module['list'])
            ->name('index')
            ->breadcrumbs(fn (Trail $trail) =>
                $trail->parent('platform.index')->push($module['label'])
            );
        Route::screen('create', $module['edit'])
            ->name('create')
            ->breadcrumbs(fn (Trail $trail) =>
                $trail->parent("platform.{$prefix}.index")->push('Criar')
            );
        Route::screen('{model}/edit', $module['edit'])
            ->name('edit')
            ->whereNumber('model')
            ->breadcrumbs(function (Trail $trail, $model) use ($prefix, $module) {
                $m = $module['model']::findOrFail($model);
                return $trail->parent("platform.{$prefix}.index")
                    ->push($m->nome ?? $m->titulo ?? $m->nome_razao_social ?? "#{$m->id}");
            });
    });
}
// --------------------------------------------------------------------------
// CONSTRUTORAS
// --------------------------------------------------------------------------
Route::prefix('construtoras')->name('platform.construtoras.')->group(function () {
    Route::screen('/', ConstrutoraListScreen::class)
        ->name('index')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.index')->push('Construtoras')
        );
    Route::screen('create-auto', ConstrutoraCreateAutoScreen::class)
        ->name('create.auto')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.construtoras.index')->push('Novo (Automático)')
        );
    Route::screen('create-manual', ConstrutoraCreateManualScreen::class)
        ->name('create.manual')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.construtoras.index')->push('Novo (Manual)')
        );
    Route::screen('{construtora}/edit', ConstrutoraEditScreen::class)
        ->name('edit')
        ->whereNumber('construtora')
        ->breadcrumbs(fn (Trail $trail, Construtora $c) =>
            $trail->parent('platform.construtoras.index')
                ->push($c->nome_razao_social ?? "#{$c->id}")
        );
});
// --------------------------------------------------------------------------
// PROPOSTAS (ROTAS COMPLETAS E CORRIGIDAS)
// --------------------------------------------------------------------------
Route::prefix('propostas')->name('platform.propostas.')->group(function () {
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
    Route::screen('create/{lead}', PropostasEditScreen::class)
        ->name('create.from.lead')
        ->whereNumber('lead')
        ->breadcrumbs(fn (Trail $trail, Lead $lead) =>
            $trail->parent('platform.propostas.index')->push("Proposta para Lead #{$lead->id}")
        );
    Route::screen('{proposta}/edit', PropostasEditScreen::class)
        ->name('edit')
        ->whereNumber('proposta')
        ->breadcrumbs(fn (Trail $trail, Proposta $p) =>
            $trail->parent('platform.propostas.index')->push("Proposta #{$p->id}")
        );
    Route::screen('{proposta}/view', PropostasEditScreen::class)
        ->name('view')
        ->breadcrumbs(fn (Trail $trail, Proposta $p) =>
            $trail->parent('platform.propostas.index')->push("Visualizar Proposta #{$p->id}")
        );
    Route::screen('arquivadas', PropostasArquivadasScreen::class)
        ->name('arquivadas')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.propostas.index')->push('Propostas Arquivadas')
        );
    Route::screen('kanban', PropostasKanbanScreen::class)
        ->name('kanban')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.propostas.index')->push('Kanban de Propostas')
        );
    // AÇÕES
    Route::post('kanban/update', [PropostasKanbanScreen::class, 'updateStatus'])
        ->name('kanban.update');
    Route::post('archive', [PropostasListScreen::class, 'archive'])
        ->name('archive');
    Route::post('unarchive', [PropostasListScreen::class, 'unarchive'])
        ->name('unarchive');
    Route::post('calculate', [PropostasEditScreen::class, 'ajaxCalculate'])
        ->name('calculate');
    Route::get('{proposta}/delete', [PropostasListScreen::class, 'remove'])
        ->name('delete')
        ->whereNumber('proposta');
    // -------------------------
    // PDF (CORRIGIDO E FINAL)
    // -------------------------
    Route::get('{proposta}/pdf', [PropostasEditScreen::class, 'generatePdf'])
        ->name('pdf')
        ->whereNumber('proposta')
        ->breadcrumbs(fn (Trail $trail, Proposta $p) =>
            $trail->parent('platform.propostas.index')->push("PDF da Proposta #{$p->id}")
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
        ->name('create')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.leads.index')->push('Criar Lead')
        );
    Route::screen('{lead}/edit', LeadEditScreen::class)
        ->name('edit')
        ->whereNumber('lead')
        ->breadcrumbs(fn (Trail $trail, Lead $lead) =>
            $trail->parent('platform.leads.index')->push($lead->nome ?? "#{$lead->id}")
        );
    Route::screen('kanban', LeadKanbanScreen::class)
        ->name('kanban')
        ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('platform.leads.index')->push('Kanban de Leads')
        );
    Route::post('kanban/update', [LeadKanbanScreen::class, 'updateKanban'])
        ->name('kanban.update');
    Route::post('kanban/update-drag', [LeadKanbanScreen::class, 'updateKanban'])
        ->name('kanban.update.drag');
    
    // ADIÇÃO IA: Rota para geração de follow-up com ChatGPT (dentro do grupo leads)
    Route::post('ai/followup', [LeadAiController::class, 'generateFollowUp'])
        ->name('ai.followup');
});
// --------------------------------------------------------------------------
// CONFIGURAÇÕES
// --------------------------------------------------------------------------
Route::screen('configuracoes', ImobiliariaConfigScreen::class)
    ->name('platform.configuracoes')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Configurações')
    );
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