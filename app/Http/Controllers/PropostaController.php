<?php

namespace App\Http\Controllers;

use App\Models\Proposta;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Browsershot\Browsershot;

class PropostaController extends Controller
{
    /**
     * Exibe o formulário de criação de proposta.
     */
    public function create()
    {
        $leads = Lead::all();
        return view('platform.propostas.create', [
            'leads' => $leads,
            'proposta' => new Proposta()
        ]);
    }

    /**
     * Valida e cria uma proposta.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'construtora_id' => ['nullable', 'exists:construtoras,id'],
            'fluxo_id' => ['nullable', 'exists:fluxos,id'],

            // Dados financeiros da proposta
            'proposta.valor_real' => ['required', 'numeric', 'min:0'],
            'proposta.valor_financiado' => ['nullable', 'numeric', 'min:0'],
            'proposta.descontos' => ['nullable', 'numeric', 'min:0'],
            'proposta.valor_assinatura' => ['nullable', 'numeric', 'min:0'],
            'proposta.valor_parcela' => ['nullable', 'numeric', 'min:0'],
            'proposta.num_parcelas' => ['nullable', 'integer', 'min:0'],
            'proposta.valor_entrada' => ['nullable', 'numeric', 'min:0'],
            'proposta.total_parcelamento' => ['nullable', 'numeric', 'min:0'],
            'proposta.valor_restante' => ['nullable', 'numeric', 'min:0'],

            // Balões
            'proposta.baloes_json' => ['nullable', 'json'],
        ]);

        $dados = $request->input('proposta');
        $dados['lead_id'] = $request->lead_id;
        $dados['construtora_id'] = $request->construtora_id;
        $dados['fluxo_id'] = $request->fluxo_id;
        $dados['status'] = 'rascunho';
        $dados['user_id'] = Auth::id();

        $proposta = Proposta::create($dados);

        return redirect()
            ->route('propostas.show', $proposta->id)
            ->with('success', 'Proposta criada com sucesso.');
    }

    /**
     * Exibe uma proposta.
     */
    public function show($id)
    {
        $proposta = Proposta::with(['lead', 'construtora', 'fluxo'])
            ->findOrFail($id);

        return view('platform.propostas.show', compact('proposta'));
    }

    /**
     * Gera o PDF da proposta usando Browsershot (Tailwind funciona).
     */
    public function gerar($id)
    {
        $proposta = Proposta::with(['lead', 'construtora', 'fluxo'])
            ->findOrFail($id);

        $html = view('platform.propostas.pdf', compact('proposta'))->render();

        return Browsershot::html($html)
            ->showBackground()
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->pdf();
    }

    /**
     * Download do PDF (salvando arquivo físico).
     */
    public function download($id)
    {
        $proposta = Proposta::with(['lead', 'construtora', 'fluxo'])
            ->findOrFail($id);

        $html = view('platform.propostas.pdf', compact('proposta'))->render();

        $filePath = storage_path("app/proposta_{$id}.pdf");

        Browsershot::html($html)
            ->showBackground()
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->savePdf($filePath);

        return response()->download($filePath);
    }
}
