@php
    $colors = [
        'novo'         => 'bg-info text-white',
        'qualificacao' => 'bg-primary',
        'visita'       => 'bg-warning text-dark',
        'negociacao'   => 'bg-orange text-white',
        'fechamento'   => 'bg-success',
        'perdido'      => 'bg-danger',
    ];
    $color = $colors[$status] ?? 'bg-secondary';
    $label = \App\Models\Lead::STATUS[$status] ?? 'Indefinido';
@endphp

<span class="badge {{ $color }} fw-semibold">
    {{ $label }}
</span>