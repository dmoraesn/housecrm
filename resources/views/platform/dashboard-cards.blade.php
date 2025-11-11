{{--
  resources/views/platform/dashboard-cards.blade.php
  Cards de métricas do Dashboard (refatorado)
  Variáveis esperadas: $vgv_mes, $corretores_time, $leads_totais, $alugueis_ativos, $imoveis_cadastrados
--}}

@php
    // Configuração centralizada dos cards
    $cards = [
        [
            'icon'   => 'bi bi-currency-dollar',
            'color'  => 'text-success',
            'title'  => 'VGV do Mês',
            'value'  => $vgv_mes ?? 0,
            'format' => 'currency', // R$ 1.234.567,89
        ],
        [
            'icon'   => 'bi bi-people',
            'color'  => 'text-info',
            'title'  => 'Corretores no Time',
            'value'  => $corretores_time ?? 0,
        ],
        [
            'icon'   => 'bi bi-list-check',
            'color'  => 'text-primary',
            'title'  => 'Leads Totais',
            'value'  => $leads_totais ?? 0,
        ],
        [
            'icon'   => 'bi bi-house-door',
            'color'  => 'text-warning',
            'title'  => 'Aluguéis Ativos',
            'value'  => $alugueis_ativos ?? 0,
        ],
        [
            'icon'   => 'bi bi-building',
            'color'  => 'text-secondary',
            'title'  => 'Imóveis Cadastrados',
            'value'  => $imoveis_cadastrados ?? 0,
        ],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach($cards as $card)
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100 d-flex flex-column justify-content-center" role="status" aria-live="polite">
                <div class="card-body text-center p-3">
                    <i class="{{ $card['icon'] }} {{ $card['color'] }} fs-1 mb-2" aria-hidden="true"></i>
                    <h6 class="card-title text-muted small mb-1">{{ $card['title'] }}</h6>
                    <h3 class="mb-0 {{ $card['color'] }} fw-bold">
                        @if(isset($card['format']) && $card['format'] === 'currency')
                            R$ {{ number_format($card['value'], 2, ',', '.') }}
                        @else
                            {{ number_format($card['value'], 0, '', '.') }}
                        @endif
                    </h3>
                </div>
            </div>
        </div>
    @endforeach
</div>