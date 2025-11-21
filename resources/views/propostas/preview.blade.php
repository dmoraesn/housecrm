<div style="padding: 20px; font-size: 14px; color:#333;">

    <h2 style="text-align:center; margin-bottom:20px;">
        PROPOSTA COMERCIAL
    </h2>

    <p><strong>Cliente:</strong> {{ $proposta->lead->nome ?? 'Não informado' }}</p>
    <p><strong>Lead ID:</strong> #{{ $proposta->lead_id }}</p>
    <p><strong>Status:</strong> {{ ucfirst($proposta->status) }}</p>

    <hr style="margin:20px 0;">

    <h3>Resumo Financeiro</h3>

    <p><strong>Valor Real do Bem:</strong>
        R$ {{ number_format($proposta->valor_real, 2, ',', '.') }}</p>

    <p><strong>Descontos:</strong>
        R$ {{ number_format($proposta->descontos, 2, ',', '.') }}</p>

    <p><strong>Valor Líquido:</strong>
        <strong>
            R$ {{ number_format($proposta->valor_real - $proposta->descontos, 2, ',', '.') }}
        </strong>
    </p>

</div>
