<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Proposta #{{ $proposta->id }}</title>

    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; margin: 30px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #1a5fb4; }
        .info table, .section table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #ccc; padding: 6px; }
        th { background: #f0f0f0; }
        .highlight { background: #e6f7ff; font-weight: bold; }
        .text-right { text-align: right; }
        .footer { margin-top: 50px; text-align: center; font-size: 11px; color: #777; }
        button.print-btn {
            position: fixed; top: 10px; right: 10px;
            padding: 8px 14px; border: none;
            background: #1a5fb4; color: #fff; border-radius: 6px;
            cursor: pointer; font-size: 12px;
        }
    </style>

</head>
<body>

<button class="print-btn" onclick="window.print()">Imprimir</button>

<div class="header">
    <h1>PROPOSTA COMERCIAL</h1>
    <p>Proposta #{{ $proposta->id }} — {{ now()->format('d/m/Y') }}</p>
</div>

<div class="info">
    <table>
        <tr><td><strong>Cliente:</strong></td><td>{{ $proposta->cliente }}</td></tr>
        <tr><td><strong>Lead ID:</strong></td><td>#{{ $proposta->lead_id }}</td></tr>
        <tr><td><strong>Status:</strong></td><td>{{ ucfirst($proposta->status) }}</td></tr>
    </table>
</div>

<div class="section">
    <h2>Resumo Financeiro</h2>

    <table>
        <tr><td>Valor Real do Bem</td>
            <td class="text-right">R$ {{ number_format($proposta->valor_real, 2, ',', '.') }}</td></tr>

        <tr><td>(-) Descontos</td>
            <td class="text-right">- R$ {{ number_format($proposta->descontos, 2, ',', '.') }}</td></tr>

        <tr class="highlight">
            <td><strong>Valor Líquido</strong></td>
            <td class="text-right"><strong>R$ {{ number_format($proposta->valor_real - $proposta->descontos, 2, ',', '.') }}</strong></td>
        </tr>

        <tr><td>Entrada Total</td>
            <td class="text-right">R$ {{ number_format($proposta->valor_entrada, 2, ',', '.') }}</td></tr>

        <tr><td>Parcelamento ({{ $proposta->num_parcelas }}x)</td>
            <td class="text-right">R$ {{ number_format($proposta->total_parcelamento, 2, ',', '.') }}</td></tr>

        <tr class="highlight">
            <td><strong>Diferença</strong></td>
            <td class="text-right">R$ {{ number_format(abs($proposta->valor_restante), 2, ',', '.') }}</td>
        </tr>
    </table>
</div>

@if(!empty($baloes))
<div class="section">
    <h2>Balões</h2>
    <table>
        <tr><th>Data</th><th class="text-right">Valor</th></tr>
        @foreach($baloes as $b)
            <tr>
                <td>{{ \Carbon\Carbon::parse($b['data'])->format('d/m/Y') }}</td>
                <td class="text-right">R$ {{ number_format($b['valor'], 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>
</div>
@endif

<div class="footer">
    HouseCRM — {{ date('Y') }}
</div>

</body>
</html>
