<?php
namespace App\Orchid\Screens;
use App\Models\Lead;
use App\Models\User;
use App\Models\Aluguel;
use App\Models\Imovel;
use App\Models\Proposta;
use App\Models\Contrato;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
class DashboardScreen extends Screen
{
    /**
     * Nome da tela.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Dashboard de CRM';
    }
    /**
     * Descrição da tela.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Visão geral do desempenho e métricas chave.';
    }
    /**
     * Busca os dados para exibição na tela.
     *
     * @return array
     */
    public function query(): array
    {
        return [
            'metrics' => $this->getMetrics(),
            'latestLeads' => Lead::latest()->take(5)->get(),
        ];
    }
    /**
     * Calcula as métricas para os cards.
     *
     * @return array
     */
    private function getMetrics(): array
    {
        $vgv = 0.0;
        $currentYear = now()->year;
        $currentMonth = now()->month;
        // VGV de Propostas
        $vgvPropostas = 0.0;
        if (Schema::hasColumn('propostas', 'valor_total')) {
            $vgvPropostas = Proposta::where('status', 'fechado')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->sum('valor_total');
        }
        $vgv += $vgvPropostas;
        // VGV de Contratos
        $vgvContratos = 0.0;
        if (Schema::hasColumn('contratos', 'valor')) {
            $vgvContratos = Contrato::where('status', 'ativo')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->sum('valor');
        }
        $vgv += $vgvContratos;
        return [
            'VGV Mês (R$)' => 'R$ ' . number_format($vgv, 2, ',', '.'),
            'Leads Totais' => number_format(Lead::count(), 0, ',', '.'),
            'Corretores no Time' => number_format(
                User::whereHas('roles', fn($q) => $q->where('slug', 'corretor'))->count(),
                0,
                ',',
                '.'
            ),
            'Imóveis Cadastrados' => number_format(
                class_exists(Imovel::class) && Schema::hasTable('imoveis') ? Imovel::count() : 0,
                0,
                ',',
                '.'
            ),
            'Aluguéis Ativos' => number_format(
                class_exists(Aluguel::class) && Schema::hasTable('alugueis')
                    ? Aluguel::where('status', 'ativo')->count()
                    : 0,
                0,
                ',',
                '.'
            ),
        ];
    }
    /**
     * Define os botões de ação da tela.
     *
     * @return array
     */
    public function commandBar(): array
    {
        return [
            Link::make('Novo Lead')
                ->route('platform.leads.create')
                ->icon('bs.plus-circle'),
            Link::make('Kanban')
                ->route('platform.leads.kanban')
                ->icon('bs.grid-3x3-gap'),
        ];
    }
    /**
     * Define o layout da tela.
     *
     * @return iterable
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'VGV Mês (R$)' => 'metrics.VGV Mês (R$)',
                'Leads Totais' => 'metrics.Leads Totais',
                'Corretores no Time' => 'metrics.Corretores no Time',
                'Imóveis Cadastrados' => 'metrics.Imóveis Cadastrados',
                'Aluguéis Ativos' => 'metrics.Aluguéis Ativos',
            ]),
            Layout::table('latestLeads', [
                TD::make('id', 'ID')
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Lead $lead) => '#' . $lead->id),
                TD::make('nome', 'Nome do Lead')
                    ->render(fn (Lead $lead) => sprintf(
                        '<div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-dark">%s</p>
                                <p class="small text-muted mb-0">Origem: %s</p>
                            </div>
                        </div>',
                        $lead->nome,
                        // CORREÇÃO APLICADA: Acessa o valor string do Enum (via ?->value) 
                        // e usa coalesce para 'Desconhecida'.
                        $lead->origem?->value ?? 'Desconhecida' 
                    )),
                TD::make('status', 'Status')
                    ->align(TD::ALIGN_CENTER)
                    // Esta linha foi corrigida na rodada anterior para acessar ->value
                    ->render(fn (Lead $lead) => ucfirst($lead->status->value)), 
                TD::make('created_at', 'Criado em')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn (Lead $lead) => Carbon::parse($lead->created_at)->format('d/m/Y H:i')),
            ]),
        ];
    }
}