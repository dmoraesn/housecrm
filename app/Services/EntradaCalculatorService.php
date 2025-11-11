<?php

namespace App\Services;

use App\Models\Proposta;
use Illuminate\Support\Collection;

class EntradaCalculatorService
{
    /**
     * Calcula os valores financeiros da proposta.
     *
     * @param  array  $dados
     * @return array
     */
    public function calcular(array $dados): array
    {
        $valorReal       = (float) ($dados['valor_real'] ?? 0);
        $valorFinanciado = (float) ($dados['valor_financiado'] ?? 0);
        $descontos       = (float) ($dados['descontos'] ?? 0);
        $valorAssinatura = (float) ($dados['valor_assinatura'] ?? 0);
        $valorParcela    = (float) ($dados['valor_parcela'] ?? 0);
        $numParcelas     = (int)   ($dados['num_parcelas'] ?? 0);

        // Decodifica balões do JSON
        $baloes = $dados['baloes_json'] ?? [];
        if (is_string($baloes)) {
            $baloes = json_decode($baloes, true) ?? [];
        }

        $baloesCollection = collect($baloes)->filter(fn ($b) =>
            isset($b['valor']) && $b['valor'] > 0
        );

        // Lógica central
        $valorLiquido = $valorReal - $descontos;
        $totalBaloes = $baloesCollection->sum('valor');
        $valorEntrada = $valorAssinatura + $totalBaloes;
        $totalParcelamento = $valorParcela * $numParcelas;
        $valorRestante = $valorLiquido - $valorEntrada - $totalParcelamento - $valorFinanciado;

        return [
            'valor_liquido'       => round($valorLiquido, 2),
            'valor_entrada'       => round($valorEntrada, 2),
            'total_parcelamento'  => round($totalParcelamento, 2),
            'valor_restante'      => round($valorRestante, 2),
            'baloes_json'         => $baloesCollection->values()->toArray(),
        ];
    }

    /**
     * Aplica o cálculo diretamente em uma instância de Proposta.
     */
    public function aplicar(Proposta $proposta): Proposta
    {
        $resultado = $this->calcular($proposta->toArray());
        $proposta->fill($resultado);
        return $proposta;
    }
}
