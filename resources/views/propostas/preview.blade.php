<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Proposta de Pagamento - {{ $construtora->nome ?? 'Construtora' }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { max-width: 150px; height: auto; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 14px; font-weight: bold; color: #007bff; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .total { font-weight: bold; background-color: #e9ecef; }
        .currency { text-align: right; }
        .observacoes { white-space: pre-wrap; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ $logo ?? public_path('images/logo.png') }}" alt="Logo" class="logo">
        <h1>Proposta de Pagamento de Entrada</h1>
        <p><strong>Data da Proposta:</strong> {{ $proposta->data_assinatura ?? now()->format('d/m/Y') }}</p>
        <p><strong>Nº da Proposta:</strong> {{ $proposta->id ?? 'N/A' }}</p>
    </div>

    <div class="section">
        <div class="section-title">Dados da Construtora</div>
        <p><strong>{{ $construtora->nome ?? 'N/A' }}</strong></p>
        <p>{{ $construtora->endereco ?? '' }} | CNPJ: {{ $construtora->cnpj ?? 'N/A' }}</p>
        <p>Telefone: {{ $construtora->telefone ?? '' }} | E-mail: {{ $construtora->email ?? '' }}</p>
    </div>

    <div class="section">
        <div class="section-title">Dados do Cliente</div>
        <p><strong>{{ $lead->nome ?? 'N/A' }}</strong></p>
        <p>CPF: {{ $lead->cpf ?? 'N/A' }} | Telefone: {{ $lead->telefone ?? '' }}</p>
        <p>E-mail: {{ $lead->email ?? '' }} | Endereço: {{ $lead->endereco ?? '' }}</p>
    </div>

    <div class="section">
        <div class="section-title">Resumo do Imóvel</div>
        <table>
            <tr>
                <td><strong>Valor do Imóvel</strong></td>
                <td class="currency">R$ {{ number_format($fluxo->valor_imovel ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Valor de Avaliação</strong></td>
                <td class="currency">R$ {{ number_format($fluxo->valor_avaliacao ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Valor Financiado</strong></td>
                <td class="currency">R$ {{ number_format($fluxo->valor_financiado ?? 0, 2, ',', '.') }} ({{ $fluxo->financiamento_percentual ?? 0 }}%)</td>
            </tr>
            <tr>
                <td><strong>Bônus / Descontos / FGTS</strong></td>
                <td class="currency">R$ {{ number_format($fluxo->valor_bonus_descontos ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Entrada Mínima Necessária</strong></td>
                <td class="currency">R$ {{ number_format($fluxo->entrada_minima ?? 0, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Plano de Pagamento</div>
        <table>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Data</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                @if($fluxo->valor_assinatura_contrato > 0)
                <tr>
                    <td>Assinatura do Contrato (Sinal)</td>
                    <td>{{ $fluxo->valor_assinatura_contrato_data ?? now()->format('d/m/Y') }}</td>
                    <td class="currency">R$ {{ number_format($fluxo->valor_assinatura_contrato ?? 0, 2, ',', '.') }}</td>
                </tr>
                @endif

                @if($fluxo->valor_na_chaves > 0)
                <tr>
                    <td>Entrega das Chaves (Balanço)</td>
                    <td>À entregar</td>
                    <td class="currency">R$ {{ number_format($fluxo->valor_na_chaves ?? 0, 2, ',', '.') }}</td>
                </tr>
                @endif

                @if($fluxo->baloes && count($fluxo->baloes) > 0)
                @foreach($fluxo->baloes as $balao)
                <tr>
                    <td>Balão de Pagamento</td>
                    <td>{{ $balao['data'] ?? '' }}</td>
                    <td class="currency">R$ {{ number_format($balao['valor'] ?? 0, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                @endif

                @if($fluxo->parcelas_qtd > 0)
                <tr class="total">
                    <td>{{ $fluxo->parcelas_qtd }} Parcelas de</td>
                    <td>Início imediato</td>
                    <td class="currency">R$ {{ number_format($fluxo->valor_parcela ?? 0, 2, ',', '.') }} cada</td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Total do Parcelamento</strong></td>
                    <td class="currency">R$ {{ number_format($fluxo->total_parcelamento ?? 0, 2, ',', '.') }}</td>
                </tr>
                @endif

                <tr class="total">
                    <td colspan="2"><strong>Valor Total da Entrada</strong></td>
                    <td class="currency">R$ {{ number_format($fluxo->valor_total_entrada ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr class="total">
                    <td colspan="2"><strong>Valor Restante (Diferença)</strong></td>
                    <td class="currency">R$ {{ number_format($fluxo->valor_restante ?? 0, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($fluxo->observacao)
    <div class="section">
        <div class="section-title">Observações</div>
        <p class="observacoes">{{ $fluxo->observacao }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Esta proposta é válida por 30 dias a partir da data de emissão. Para dúvidas, contate a construtora.</p>
        <p>Gerado pelo Sistema HouseCRM em {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>