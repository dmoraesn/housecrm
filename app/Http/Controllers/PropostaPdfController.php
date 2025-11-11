<?php

namespace App\Http\Controllers;

use App\Models\Proposta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PropostaPdfController extends Controller
{
    public function generate(Proposta $proposta)
    {
        // Carrega balões
        $baloes = $proposta->baloes_json ?? [];

        $pdf = Pdf::loadView('platform.propostas.pdf', compact('proposta', 'baloes'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("proposta-{$proposta->id}.pdf");
    }
}