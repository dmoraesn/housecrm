@php
    $colors = [
        'novo'         => 'bg-info text-white',
        'qualificacao' => 'bg-primary text-white',
        'visita'       => 'bg-warning text-dark',
        'negociacao'   => 'bg-warning text-dark',
        'fechamento'   => 'bg-success text-white',
        'perdido'      => 'bg-danger text-white',
        'ativo'        => 'bg-success text-white',
        'arquivada'    => 'bg-secondary text-white',
        'rascunho'     => 'bg-light text-dark',
    ];
    $label = ($status == 'ativo' ? 'Ativa' : ($status == 'arquivada' ? 'Arquivada' : ($status == 'rascunho' ? 'Rascunho' : 'Indefinido')));
@endphp
<span class="badge {{ $colors[$status] ?? 'bg-secondary text-white' }} fw-semibold">
    {{ $label }}
</span>