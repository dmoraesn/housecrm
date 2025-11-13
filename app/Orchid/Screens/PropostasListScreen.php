<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Illuminate\Support\HtmlString;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use App\Models\Proposta;
use App\Models\Fluxo;
use PDF;

/**
 * Tela de listagem de Propostas de Pagamento.
 *
 * Exibe:
 * - Nome do Lead
 * - Data de Assinatura de Contrato
 * - R$ Sinal (valor_assinatura_contrato)
 * - Ações: Editar, Arquivar, Baixar PDF
 */
class PropostasListScreen extends Screen
{
    public $name = 'Propostas de Pagamento';
    public $description = 'Gerenciamento de propostas.';

    public function commandBar(): array
    {
        return [
            Link::make('Nova Proposta')
                ->icon('bs.plus-circle')
                ->route('platform.fluxo'),
        ];
    }

    public function query(): array
    {
        return [
            'propostas' => Proposta::with(['lead', 'fluxo'])
                ->whereHas('fluxo', function ($q) {
                    $q->where('status', Fluxo::STATUS_COMPLETED);
                })
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('propostas', [
                TD::make('lead.nome', 'Nome do Lead')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn($p) => $p->lead
                        ? Link::make($p->lead->nome)->route('platform.leads.edit', $p->lead)
                        : 'N/A'),

                TD::make('fluxo.valor_assinatura_contrato_data', 'Data de Assinatura de Contrato')
                    ->sort()
                    ->render(fn($p) => $p->fluxo?->valor_assinatura_contrato_data
                        ? \Carbon\Carbon::parse($p->fluxo->valor_assinatura_contrato_data)->format('d/m/Y')
                        : '—'),

                TD::make('fluxo.valor_assinatura_contrato', 'R$ Sinal')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn($p) => $p->fluxo?->valor_assinatura_contrato_formatted
                        ?? 'R$ 0,00'),

                TD::make('acoes', 'Opções')
                    ->align(TD::ALIGN_CENTER)
                    ->width('120px')
                    ->render(function ($p) {
                        return DropDown::make()
                            ->icon('bs.three-dots-vertical')
                            ->list([
                                Link::make('Editar')
                                    ->icon('bs.pencil')
                                    ->route('platform.fluxo', ['fluxo' => $p->fluxo_id]), // ← ROTA CORRETA

                                Button::make('Arquivar')
                                    ->icon('bs.archive')
                                    ->method('arquivar', ['proposta' => $p->id])
                                    ->confirm('Tem certeza que deseja arquivar esta proposta?'),

                                Button::make('Baixar PDF')
                                    ->icon('bs.file-earmark-pdf')
                                    ->method('baixarPdf', ['proposta' => $p->id])
                                    ->novalidate(),
                            ]);
                    }),
            ]),
        ];
    }

    /**
     * Arquiva a proposta (muda status para arquivado).
     */
    public function arquivar($propostaId)
    {
        $proposta = Proposta::findOrFail($propostaId);
        $proposta->status = 'arquivado';
        $proposta->save();

        \Orchid\Support\Facades\Toast::info('Proposta arquivada com sucesso.');
    }

    /**
     * Gera e baixa o PDF da proposta.
     */
    public function baixarPdf($propostaId)
    {
        $proposta = Proposta::with(['lead', 'fluxo'])->findOrFail($propostaId);
        $fluxo = $proposta->fluxo;

        if (!$fluxo) {
            \Orchid\Support\Facades\Toast::error('Fluxo não encontrado.');
            return;
        }

        // Formata valores via acessors
        $fluxo->append([
            'valor_imovel_formatted',
            'valor_avaliacao_formatted',
            'valor_financiado_formatted',
            'valor_bonus_descontos_formatted',
            'entrada_minima_formatted',
            'valor_assinatura_contrato_formatted',
            'valor_na_chaves_formatted',
            'valor_parcela_formatted',
            'total_parcelamento_formatted',
            'valor_total_entrada_formatted',
            'valor_restante_formatted',
        ]);

        $pdf = PDF::loadView('pdf.proposta', compact('proposta', 'fluxo'));

        $filename = 'proposta_' . ($proposta->lead?->nome ?? 'sem_lead') . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}