<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fluxo de Entrada - {{ $fluxo->lead?->nome ?? 'Sem Lead' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; color: #1a5fb4; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 14px; background: #f0f0f0; padding: 8px; margin: 0 0 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; }
        .text-right { text-align: right; }
        .mt-2 { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Plano de Pagamento de Entrada</h1>
        <p>Calculadora Pro Soluto</p>
    </div>

    @if($fluxo->lead)
        <p><strong>Lead:</strong> {{ $fluxo->lead->nome }}</p>
    @endif

    <div class="section">
        <h2>1. Resumo do Imóvel e Financiamento</h2>
        <table>
            <tr><th>Valor do Imóvel</th><td>{{ $fluxo->valor_imovel_formatted }}</td></tr>
            <tr><th>Valor de Avaliação</th><td>{{ $fluxo->valor_avaliacao_formatted }}</td></tr>
            <tr><th>Base de Cálculo</th><td>{{ ucfirst($fluxo->base_calculo) }}</td></tr>
            <tr><th>Modo de Cálculo</th><td>{{ $fluxo->modo_calculo === 'percentual' ? 'Percentual' : 'Manual' }}</td></tr>
            <tr><th>Financiamento (%)</th><td>{{ number_format($fluxo->financiamento_percentual, 0) }}%</td></tr>
            <tr><th>Valor Financiado</th><td>{{ $fluxo->valor_financiado_formatted }}</td></tr>
            <tr><th>Bônus / FGTS</th><td>{{ $fluxo->valor_bonus_descontos_formatted }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Entrada Mínima Necessária</h2>
        <p class="text-right"><strong>{{ $fluxo->entrada_minima_formatted }}</strong></p>
    </div>

    <div class="section">
        <h2>2. Valores Altos</h2>
        <table>
            <tr><th>Assinatura do Contrato</th><td>{{ $fluxo->valor_assinatura_contrato_formatted }}</td></tr>
            <tr><th>Entrega das Chaves</th><td>{{ $fluxo->valor_na_chaves_formatted }}</td></tr>
        </table>
    </div>

    @if($fluxo->baloes && count($fluxo->baloes) > 0)
        <div class="section">
            <h2>3. Balões de Pagamento</h2>
            <table>
                <thead>
                    <tr><th>Data</th><th>Valor</th></tr>
                </thead>
                <tbody>
                    @foreach($fluxo->baloes as $balao)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($balao['data'] ?? '')->format('d/m/Y') }}</td>
                            <td>{{ 'R$ ' . number_format($balao['valor'] ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="section">
        <h2>4. Parcelamento Mensal</h2>
        <table>
            <tr><th>Número de Parcelas</th><td>{{ $fluxo->parcelas_qtd ?? 0 }}</td></tr>
            <tr><th>Valor por Parcela</th><td>{{ $fluxo->valor_parcela_formatted }}</td></tr>
            <tr><th>Total do Parcelamento</th><td>{{ $fluxo->total_parcelamento_formatted }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Resumo da Entrada</h2>
        <table>
            <tr><th>Valor Total da Entrada</th><td>{{ $fluxo->valor_total_entrada_formatted }}</td></tr>
            <tr><th>Valor Restante</th><td>{{ $fluxo->valor_restante_formatted }}</td></tr>
        </table>
    </div>

    @if($fluxo->observacao)
        <div class="section mt-2">
            <h2>Observações</h2>
            <p>{!! nl2br(e($fluxo->observacao)) !!}</p>
        </div>
    @endif

    <div class="text-center mt-2" style="font-size: 10px; color: #666;">
        Gerado em {{ now()->format('d/m/Y \à\s H:i') }} • HouseCRM
    </div>
</body>
</html>