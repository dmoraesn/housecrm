<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Proposta #{{ $proposta->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #1a5fb4; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; border-collapse: collapse; }
        .info td { padding: 5px 0; }
        .section { margin: 25px 0; }
        .section h2 { color: #1a5fb4; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .highlight { background-color: #e6f7ff; font-weight: bold; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PROPOSTA COMERCIAL</h1>
        <p>Proposta #{{ $proposta->id }} | {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td><strong>Cliente:</strong></td>
                <td>{{ $proposta->cliente ?? 'Não informado' }}</td>
            </tr>
            <tr>
                <td><strong>Lead ID:</strong></td>
                <td>#{{ $proposta->lead_id }}</td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>{{ ucfirst($proposta->status) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Resumo Financeiro</h2>
        <table>
            <tr>
                <td>Valor Real do Bem</td>
                <td class="text-right">R$ {{ number_format($proposta->valor_real ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>(-) Descontos</td>
                <td class="text-right text-danger">- R$ {{ number_format($proposta->descontos ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr class="highlight">
                <td><strong>Valor Líquido</strong></td>
                <td class="text-right"><strong>R$ {{ number_format(($proposta->valor_real ?? 0) - ($proposta->descontos ?? 0), 2, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Valor a Financiar</td>
                <td class="text-right">R$ {{ number_format($proposta->valor_financiado ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Entrada Total</td>
                <td class="text-right text-success">R$ {{ number_format($proposta->valor_entrada ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Parcelamento ({{ $proposta->num_parcelas }}x)</td>
                <td class="text-right">R$ {{ number_format($proposta->total_parcelamento ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr class="highlight">
                <td><strong>Diferença</strong></td>
                <td class="text-right {{ ($proposta->valor_restante ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                    <strong>R$ {{ number_format(abs($proposta->valor_restante ?? 0), 2, ',', '.') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    @if(!empty($baloes))
    <div class="section">
        <h2>Balões de Pagamento</h2>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($baloes as $balao)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($balao['data'])->format('d/m/Y') }}</td>
                    <td class="text-right">R$ {{ number_format($balao['valor'], 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>HouseCRM - Sistema de Gestão Imobiliária</p>
        <p>Gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>