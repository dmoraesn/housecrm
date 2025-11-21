<!DOCTYPE html>
<html>
<head>
    <title>Proposta #{{ $proposta->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Proposta #{{ $proposta->id }}</h1>
        <p>Data: {{ $proposta->created_at->format('d/m/Y') }}</p>
    </div>
    <div class="details">
        <p><strong>Cliente:</strong> {{ $proposta->lead->nome ?? 'Não informado' }}</p>
        <p><strong>Valor do Imóvel:</strong> R$ {{ number_format($proposta->valor_real ?? $proposta->valor_avaliacao ?? 0, 2, ',', '.') }}</p>
        <p><strong>Status:</strong> {{ $proposta->status }}</p>
    </div>
</body>
</html>