<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
   use App\Services\EntradaCalculatorService;

public function createOrUpdate(Proposta $proposta, Request $request, EntradaCalculatorService $calc)
{
    $data = $request->input('proposta', []);

    if (empty($data['lead_id'])) {
        Toast::error('Selecione um cliente (lead).');
        return back()->withInput();
    }

    $lead = Lead::find($data['lead_id']);
    if (!$lead) {
        Toast::error('Lead não encontrado.');
        return back()->withInput();
    }

    $data['cliente'] = $lead->nome ?? 'Cliente Desconhecido';

    // Calcula valores baseados no backend
    $calculado = $calc->calcular($data);
    $data = array_merge($data, $calculado);

    try {
        $proposta->fill($data)->save();
        Toast::success('Proposta salva com sucesso!');
    } catch (\Throwable $e) {
        Toast::error('Erro ao salvar: ' . $e->getMessage());
        return back()->withInput();
    }

    return redirect()->route('platform.propostas.edit', $proposta);
}

}
