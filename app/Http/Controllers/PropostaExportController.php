<?php

namespace App\Http\Controllers;

use App\Models\Proposta;
use Illuminate\Http\Request;

class PropostaExportController extends Controller
{
    public function __invoke()
    {
        $filename = 'propostas_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($handle, [
                'ID', 'Cliente', 'Lead', 'Corretor', 'Valor Total', 'Valor Real',
                'Entrada Calculada', 'Status', 'Enviada em', 'Criada em'
            ], ';');

            Proposta::with('lead.corretor')
                ->orderBy('id', 'desc')
                ->chunk(100, function ($propostas) use ($handle) {
                    foreach ($propostas as $p) {
                        fputcsv($handle, [
                            $p->id,
                            $p->cliente,
                            $p->lead?->nome ?? '—',
                            $p->lead?->corretor?->name ?? '—',
                            $p->formatarMoeda($p->valor),
                            $p->valor_real ? $p->formatarMoeda($p->valor_real) : '—',
                            $p->formatarMoeda($p->valor_entrada_calculado),
                            ucfirst($p->status ?? 'pendente'),
                            $p->data_envio?->format('d/m/Y H:i') ?? '—',
                            $p->created_at->format('d/m/Y H:i'),
                        ], ';');
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}