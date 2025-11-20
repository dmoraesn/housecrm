@php
    $colors = [
        'novo'         => 'bg-info text-white',
        'qualificacao' => 'bg-primary text-white', // Adicionado text-white
        'visita'       => 'bg-warning text-dark',
        'negociacao'   => 'bg-warning text-dark',  // CORRIGIDO: Usa bg-warning (amarelo) com texto ESCURO
        'fechamento'   => 'bg-success text-white', // Adicionado text-white
        'perdido'      => 'bg-danger text-white',  // Adicionado text-white
    ];
    $color = $colors[$status] ?? 'bg-secondary text-white';
    $label = \App\Models\Lead::STATUS[$status] ?? 'Indefinido';
@endphp

<span class="badge {{ $color }} fw-semibold">
    {{ $label }}
</span>