@php
    // Adicionado: Se $status for um objeto (como um Model), extrai a string 'status' para evitar 'Illegal offset type'.
    if (is_object($status) && property_exists($status, 'status')) {
        $status = $status->status;
    }

    $statusString = is_string($status) ? strtolower($status) : ''; // Garante que é string ou vazia
    $colors = [
        'novo'          => 'bg-info text-white',
        'qualificacao' => 'bg-primary text-white',
        'visita'       => 'bg-warning text-dark',
        'negociacao'   => 'bg-warning text-dark',
        'fechamento'   => 'bg-success text-white',
        'perdido'      => 'bg-danger text-white',
        'ativo'        => 'bg-success text-white',
        'arquivada'    => 'bg-secondary text-white',
        'rascunho'     => 'bg-light text-dark',
    ];
    
    // Acessa a cor usando a string, com fallback para string vazia
    $colorClass = $colors[$statusString] ?? 'bg-secondary text-white';

    // Determina o label com base na string do status
    $label = match ($statusString) {
        'ativo' => 'Ativa',
        'arquivada' => 'Arquivada',
        'rascunho' => 'Rascunho',
        default => ($status ?? 'Indefinido')
    };

    // Ajusta o label para status de Lead (se necessário)
    if (array_key_exists($statusString, $colors)) {
        // Assume que a primeira letra deve ser maiúscula para exibição
        $label = ucfirst($statusString);
    }

    // Se a label for Indefinido e o status não for string, usa o fallback 'Indefinido'
    if($label === 'Indefinido') {
        $label = 'Indefinido';
    }
@endphp
<span class="badge {{ $colorClass }} fw-semibold">
    {{ $label }}
</span>