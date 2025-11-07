<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

// --------------------------------------------------------------------------
// Core Screens (Orchid + Sistema)
// --------------------------------------------------------------------------
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\LeadKanbanScreen;

// --------------------------------------------------------------------------
// Módulos Customizados
// --------------------------------------------------------------------------
use App\Orchid\Screens\DashboardScreen;
use App\Orchid\Screens\ClienteListScreen;
use App\Orchid\Screens\ClienteEditScreen;
use App\Orchid\Screens\ImovelListScreen;
use App\Orchid\Screens\ImovelEditScreen;
use App\Orchid\Screens\AluguelListScreen;
use App\Orchid\Screens\AluguelEditScreen;
use App\Orchid\Screens\ContratoListScreen;
use App\Orchid\Screens\ContratoEditScreen;
use App\Orchid\Screens\ComissaoListScreen;
use App\Orchid\Screens\ComissaoEditScreen;
use App\Orchid\Screens\PropostaListScreen;
use App\Orchid\Screens\PropostaEditScreen;
use App\Orchid\Screens\LeadListScreen;
use App\Orchid\Screens\LeadEditScreen;
use App\Orchid\Screens\ConstrutoraListScreen;
use App\Orchid\Screens\ConstrutoraEditScreen;
use App\Http\Controllers\LeadController;

// --------------------------------------------------------------------------
// Dashboard
// --------------------------------------------------------------------------
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

Route::screen('dashboard', DashboardScreen::class)
    ->name('platform.dashboard')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Dashboard', route('platform.dashboard'))
    );

// --------------------------------------------------------------------------
// Usuários e Perfis
// --------------------------------------------------------------------------
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Perfil')
    );

Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Usuários')
    );

Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.systems.users')->push('Criar Usuário')
    );

Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) =>
        $trail->parent('platform.systems.users')->push($user->name)
    );

// --------------------------------------------------------------------------
// Papéis e Permissões
// --------------------------------------------------------------------------
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Papéis e Permissões')
    );

Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.systems.roles')->push('Criar Papel')
    );

Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) =>
        $trail->parent('platform.systems.roles')->push($role->name)
    );

// --------------------------------------------------------------------------
// Módulos do CRM
// --------------------------------------------------------------------------

// 🏗️ Construtoras
Route::screen('construtoras', ConstrutoraListScreen::class)
    ->name('platform.construtoras')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Construtoras')
    );
Route::screen('construtoras/create', ConstrutoraEditScreen::class)
    ->name('platform.construtoras.create')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.construtoras')->push('Criar Construtora')
    );
Route::screen('construtoras/{construtora}/edit', ConstrutoraEditScreen::class)
    ->name('platform.construtoras.edit')
    ->breadcrumbs(fn (Trail $trail, $construtora) =>
        $trail->parent('platform.construtoras')->push($construtora->name)
    );

// 👥 Clientes
Route::screen('clientes', ClienteListScreen::class)
    ->name('platform.clientes')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Clientes')
    );
Route::screen('clientes/create', ClienteEditScreen::class)
    ->name('platform.clientes.create')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.clientes')->push('Criar Cliente')
    );
Route::screen('clientes/{cliente}/edit', ClienteEditScreen::class)
    ->name('platform.clientes.edit')
    ->breadcrumbs(fn (Trail $trail, $cliente) =>
        $trail->parent('platform.clientes')->push($cliente->nome_razao_social)
    );

// 🏠 Imóveis
Route::screen('imoveis', ImovelListScreen::class)
    ->name('platform.imoveis')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Imóveis')
    );
Route::screen('imoveis/create', ImovelEditScreen::class)
    ->name('platform.imoveis.create')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.imoveis')->push('Criar Imóvel')
    );
Route::screen('imoveis/{imovel}/edit', ImovelEditScreen::class)
    ->name('platform.imoveis.edit')
    ->breadcrumbs(fn (Trail $trail, $imovel) =>
        $trail->parent('platform.imoveis')->push($imovel->descricao)
    );

// 🏘️ Aluguéis
Route::screen('alugueis', AluguelListScreen::class)
    ->name('platform.alugueis')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Aluguéis')
    );
Route::screen('alugueis/create', AluguelEditScreen::class)
    ->name('platform.alugueis.create')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.alugueis')->push('Criar Aluguel')
    );
Route::screen('alugueis/{aluguel}/edit', AluguelEditScreen::class)
    ->name('platform.alugueis.edit')
    ->breadcrumbs(fn (Trail $trail, $aluguel) =>
        $trail->parent('platform.alugueis')->push("Aluguel #".$aluguel->id)
    );

// 📄 Contratos
Route::screen('contratos', ContratoListScreen::class)
    ->name('platform.contratos')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Contratos')
    );
Route::screen('contratos/create', ContratoEditScreen::class)
    ->name('platform.contratos.create')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.contratos')->push('Criar Contrato')
    );
Route::screen('contratos/{contrato}/edit', ContratoEditScreen::class)
    ->name('platform.contratos.edit')
    ->breadcrumbs(fn (Trail $trail, $contrato) =>
        $trail->parent('platform.contratos')->push("Contrato #".$contrato->id)
    );

// 💰 Comissões
Route::screen('comissoes', ComissaoListScreen::class)
    ->name('platform.comissoes')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Comissões')
    );
Route::screen('comissoes/create', ComissaoEditScreen::class)
    ->name('platform.comissoes.create')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.comissoes')->push('Criar Comissão')
    );
Route::screen('comissoes/{comissao}/edit', ComissaoEditScreen::class)
    ->name('platform.comissoes.edit')
    ->breadcrumbs(fn (Trail $trail, $comissao) =>
        $trail->parent('platform.comissoes')->push("Comissão #".$comissao->id)
    );

// 📑 Propostas
Route::screen('propostas', PropostaListScreen::class)
    ->name('platform.propostas')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Propostas')
    );
Route::screen('propostas/create', PropostaEditScreen::class)
    ->name('platform.propostas.create')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.propostas')->push('Criar Proposta')
    );
Route::screen('propostas/{proposta}/edit', PropostaEditScreen::class)
    ->name('platform.propostas.edit')
    ->breadcrumbs(fn (Trail $trail, $proposta) =>
        $trail->parent('platform.propostas')->push("Proposta #".$proposta->id)
    );

// 🎯 Leads (Ajustado para ter rotas separadas de create e edit)
Route::screen('leads', LeadListScreen::class)
    ->name('platform.leads')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.index')->push('Leads')
    );

// Rota de Criação (necessária para o botão "Novo Lead")
Route::screen('leads/create', LeadEditScreen::class)
    ->name('platform.leads.create')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.leads')->push('Criar Lead')
    );

// Rota de Edição
Route::screen('leads/{lead}/edit', LeadEditScreen::class)
    ->name('platform.leads.edit')
    ->breadcrumbs(fn (Trail $trail, $lead) =>
        $trail->parent('platform.leads')->push("Lead #".$lead->id)
    );

Route::screen('leads/kanban', LeadKanbanScreen::class)
    ->name('platform.leads.kanban')
    ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('platform.leads')->push('Kanban')
    );

Route::post('/admin/leads/update-status', [LeadController::class, 'updateStatus'])
    ->name('leads.updateStatus');

// --------------------------------------------------------------------------
// Padrões do Orchid (Exemplos)
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

Route::screen('example', ExampleScreen::class)->name('platform.example');
Route::screen('examples/layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
Route::screen('examples/form/fields', ExampleFieldsScreen::class)->name('platform.example.fields');
Route::screen('examples/form/advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');
Route::screen('examples/form/editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
Route::screen('examples/form/actions', ExampleActionsScreen::class)->name('platform.example.actions');
Route::screen('examples/grid', ExampleGridScreen::class)->name('platform.example.grid');
Route::screen('examples/charts', ExampleChartsScreen::class)->name('platform.example.charts');
Route::screen('examples/cards', ExampleCardsScreen::class)->name('platform.example.cards');
