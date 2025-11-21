<?php

namespace App\Http\Controllers;

use App\Models\Proposta;
use Illuminate\Http\Request;

class PropostaPdfController extends Controller
{
    public function view(Proposta $proposta)
    {
        $baloes = $proposta->baloes_json ?? [];

        return view('propostas.pdf', [
            'proposta' => $proposta,
            'baloes'   => $baloes,
        ]);
    }
}
