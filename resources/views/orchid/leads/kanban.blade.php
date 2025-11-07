@extends('platform::app')

@section('title', 'Leads')

@section('content')
<div class="kanban-board flex flex-wrap gap-4 justify-start">


    @foreach($stages as $key => $stage)
    <div class="kanban-column bg-column-bg rounded-lg p-3 shadow-md min-w-[250px] flex-shrink-0" data-stage="{{ $key }}">
        <div class="flex justify-between items-center mb-3 border-b-2 pb-2 border-{{ $stage['color'] }}">
            <h2 class="text-lg font-bold text-gray-800">{{ $stage['title'] }}</h2>
            <span class="text-xs font-semibold text-gray-600">({{ $stage['leads']->count() }} Leads)</span>
        </div>
        <div class="kanban-cards space-y-2 min-h-[100px]">
            @forelse($stage['leads'] as $lead)
            <div class="kanban-card bg-card-bg rounded-lg p-2 shadow-sm border-l-2 border-{{ $stage['color'] }} cursor-move"
                data-id="{{ $lead->id }}">
                <div class="card-title text-gray-900 font-semibold text-sm truncate">{{ $lead->nome }}</div>
                <div class="card-info text-gray-600 text-xs">
                    <p>Origem: {{ $lead->origem ?? '—' }}</p>
                    <p>Tel: {{ $lead->telefone ?? '—' }}</p>
                </div>
                <span class="inline-block bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded-full mt-1">
                    Corretor: {{ $lead->user->name ?? 'A definir' }}
                </span>
            </div>
            @empty
            <div class="text-xs text-gray-400 italic text-center py-4">Sem leads</div>
            @endforelse
        </div>
    </div>
    @endforeach

</div>
@endsection

@push('styles')
<style>
    body {
        background-color: #f4f7f6;
    }
    .kanban-column {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kanban-card {
        cursor: grab;
        transition: transform 0.1s;
    }
    .kanban-card:active {
        transform: scale(1.01);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.kanban-cards').forEach(column => {
        new Sortable(column, {
            group: 'leads',
            animation: 150,
            onEnd: function (evt) {
                const leadId = evt.item.dataset.id;
                const newStage = evt.to.closest('[data-stage]').dataset.stage;

                fetch('{{ route('leads.updateStatus') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id: leadId, status: newStage })
                })
                .then(res => res.json())
                .then(data => console.log('Lead atualizado:', data))
                .catch(err => console.error('Erro ao mover lead:', err));
            }
        });
    });
});
</script>
@endpush
