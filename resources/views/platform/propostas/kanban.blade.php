{{-- resources/views/platform/propostas/kanban.blade.php --}}

{{-- A INJEÇÃO DE ASSETS CORRETA RESOLVE OS ERROS DE MIME TYPE --}}
@push('head')
    <link rel="stylesheet" href="{{ asset('vendor/orchid/css/kanban.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endpush

@push('scripts')
    <script src="{{ asset('js/kanban.js') }}"></script>
@endpush

<div id="kanban-wrapper" 
     class="kanban-container row g-3"
     {{-- Garanta que a URL de update aponta para o método da sua Screen --}}
     data-update-url="{{ route('platform.propostas.kanban.update') }}" 
     role="region"
     aria-label="Kanban de Propostas">
    
    {{-- Aqui vai a lógica @foreach para renderizar as colunas e cards de Propostas --}}

</div>