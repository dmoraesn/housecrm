@extends('platform::app')

@section('title', 'Kanban de Leads')

@push('head')
    <link rel="stylesheet" href="{{ asset('vendor/orchid/css/kanban.min.css') }}" data-turbo-track="reload">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
@endpush

@push('scripts')
    <script src="{{ asset('js/kanban.min.js') }}" defer data-turbo-track="reload"></script>
@endpush

@section('content')
@php
    $statusColors = [
        'novo'         => '#6c757d',
        'qualificacao' => '#0dcaf0',
        'visita'       => '#fd7e14',
        'negociacao'   => '#0d6efd',
        'fechamento'   => '#198754',
        'perdido'      => '#dc3545',
        'default'      => '#adb5bd',
    ];
    $statusKeys = array_keys($statuses);
@endphp

<div id="kanban-wrapper"
     class="kanban-container row g-3"
     data-update-url="{{ route('platform.leads.kanban.update') }}"
     role="region"
     aria-label="Kanban de Leads">

    @foreach($statuses as $status => $label)
        @php
            $leadsInStatus = $leads->get($status, collect());
            $stepNumber = array_search($status, $statusKeys) + 1;
            $columnColor = $statusColors[$status] ?? $statusColors['default'];
        @endphp

        <div class="col-md-3 kanban-column-wrapper">
            <div class="card h-100 shadow-sm">
                <div class="card-header" data-status="{{ $status }}" style="background-color: {{ $columnColor }}">
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-number">{{ $stepNumber }}</span>
                        <span>{{ Str::before($label, ' /') }}</span>
                    </div>
                    <span class="badge">{{ $leadsInStatus->count() }}</span>
                </div>

                <div class="card-body p-2 kanban-column" data-status="{{ $status }}" role="listbox">
                    @forelse($leadsInStatus as $lead)
                        @include('platform.leads.partials._kanban-card', [
                            'lead' => $lead,
                            'status' => $status,
                            'columnColor' => $columnColor
                        ])
                    @empty
                        <div class="text-center text-muted py-3 small fst-italic">
                            Nenhum lead nesta etapa
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection