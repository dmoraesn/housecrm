<?php

namespace App\Http\Controllers;

use App\Models\Proposta;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropostaController extends Controller
{
    /**
     * Exibe o formulário para criação de uma nova proposta.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $leads = Lead::all(); // Carrega leads para seleção, assumindo que o imóvel vem do lead
        $proposta = new Proposta(); // Instância vazia para o formulário
        return view('platform.propostas.create', compact('leads', 'proposta'));
    }

    /**
     * Armazena uma nova proposta no banco de dados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validação dos dados da proposta
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'imovel' => 'required|string|max:255', // Adiciona validação para o campo 'imovel', assumindo que é uma string ou ID
            'proposta.valor_real' => 'required|numeric|min:0',
            'proposta.valor_financiado' => 'nullable|numeric|min:0',
            'proposta.descontos' => 'nullable|numeric|min:0',
            'proposta.valor_assinatura' => 'nullable|numeric|min:0',
            'proposta.valor_parcela' => 'nullable|numeric|min:0',
            'proposta.num_parcelas' => 'nullable|integer|min:0',
            'proposta.valor_entrada' => 'nullable|numeric|min:0',
            'proposta.total_parcelamento' => 'nullable|numeric|min:0',
            'proposta.valor_restante' => 'nullable|numeric',
            'proposta.baloes_json' => 'nullable|json',
        ]);

        // Extrai os dados da proposta
        $propostaData = $request->input('proposta');

        // Adiciona campos adicionais
        $propostaData['lead_id'] = $request->input('lead_id');
        $propostaData['imovel'] = $request->input('imovel'); // Garante que 'imovel' seja incluído
        $propostaData['status'] = 'rascunho'; // Status padrão conforme erro
        $propostaData['user_id'] = Auth::id(); // Associa ao usuário autenticado, se aplicável

        // Cria a proposta
        $proposta = Proposta::create($propostaData);

        return redirect()->route('propostas.show', $proposta->id)->with('success', 'Proposta criada com sucesso.');
    }

    // Outros métodos CRUD podem ser adicionados conforme necessário, como index, show, edit, update, destroy
}