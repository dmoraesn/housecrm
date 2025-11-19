@php
    /**
     * resources/views/platform/leads/kanban.blade.php
     * View para o Kanban de Leads, renderizada por uma Orchid Screen.
     */
@endphp

<div class="d-flex justify-content-end mb-2 mt-3">
    <button id="kanban-refresh-btn" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-arrow-clockwise me-1"></i>
        Desbloquear Movimentação de cards
    </button>
</div>



{{-- Conteúdo principal do Kanban --}}
<div class="container-fluid mt-3">
    <div id="kanban-wrapper"
         data-update-url="{{ route('platform.leads.kanban.update') }}"
         class="row g-4">

        @foreach ($columns as $column)
            @php
                $statusSlug = \Illuminate\Support\Str::slug(strtolower($column->name));
            @endphp

            <div class="col-12 col-md-4 status-{{ $statusSlug }}">
                <div class="card shadow-sm border-0 h-100">
                    {{-- Cabeçalho da Coluna --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>{{ $column->name }}</strong>
                        <span class="badge bg-light text-dark">{{ $column->leads->count() }}</span>
                    </div>

                    {{-- Corpo da Coluna (Área arrastável sem scroll forçado) --}}
                    <div class="card-body p-2">
                        <div class="kanban-column"
                             data-status="{{ $column->status }}"
                             id="kanban-column-{{ $column->status }}">

                            @forelse ($column->leads as $lead)
                                <div class="kanban-card mb-2 p-3 bg-white border rounded"
                                     data-id="{{ $lead->id }}"
                                     data-status="{{ $column->status }}"
                                     data-order="{{ $lead->order ?? 0 }}"
                                     draggable="true">

                                    <h6 class="mb-1">
                                        {{ $lead->nome ?? $lead->name ?? 'Lead #' . $lead->id }}
                                    </h6>
                                    <small class="text-muted">
                                        Corretor: {{ optional($lead->corretor)->nome ?? 'Não atribuído' }}
                                    </small>

                                    @if (isset($lead->propostas) && $lead->propostas->count())
                                        <span class="badge bg-info ms-2">
                                            {{ $lead->propostas->count() }} Proposta(s)
                                        </span>
                                    @endif
                                    @if (isset($lead->contratos) && $lead->contratos->count())
                                        <span class="badge bg-success ms-2">
                                            {{ $lead->contratos->count() }} Contrato(s)
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted text-center small kanban-empty-placeholder">Nenhum lead nesta etapa.</p>
                            @endforelse
                        </div>
                        
                        {{-- Placeholder para o botão "Ver Mais Cards" --}}
                        <div class="view-more-button-placeholder"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

---
@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* Estilos Gerais do Kanban */
    .kanban-column {
        min-height: 200px; 
        padding: 10px;
        background-color: #f0f0f0; 
        border-radius: 4px;
        overflow-y: visible;
    }
    
    .kanban-column:not(.show-all) .kanban-card:nth-child(n+6) {
        display: none;
    }

    .kanban-card {
        padding: 10px;
        margin-bottom: 10px;
        background-color: #fff;
        border: 1px solid #ddd;
        cursor: grab;
        transition: box-shadow 0.2s ease;
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    .kanban-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .kanban-ghost {
        opacity: 0.4 !important;
        background-color: #e9ecef !important;
        border: 2px dashed #aaa !important;
        box-shadow: none !important;
    }
    .kanban-loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .view-more-button {
        display: none;
        text-align: center;
        padding: 5px;
        margin-top: 10px;
        font-size: 0.9em;
        color: #007bff;
        cursor: pointer;
        border-top: 1px solid #e9ecef;
        background-color: #ffffff;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .view-more-button.visible {
        display: block;
    }

    .status-novo .card-header { background-color: #007bff; color: #fff; }
    .status-qualificacao .card-header { background-color: #ffc107; color: #343a40; }
    .status-visita .card-header { background-color: #28a745; color: #fff; }
    .status-negociacao .card-header { background-color: #fd7e14; color: #fff; }
    .status-fechamento .card-header { background-color: #6f42c1; color: #fff; }
    .status-perdido .card-header { background-color: #dc3545; color: #fff; }

    .kanban-toast {
        position: fixed;
        top: 10px;
        right: 10px;
        padding: 10px 20px;
        border-radius: 5px;
        color: #fff;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .kanban-toast.show { opacity: 1; }
    .kanban-toast.success { background-color: #28a745; }
    .kanban-toast.danger { background-color: #dc3545; }

    .kanban-blink-success { animation: blink-success 0.7s; }
    .kanban-blink-danger { animation: blink-danger 0.7s; }

    @keyframes blink-success { 50% { background-color: #d4edda; } }
    @keyframes blink-danger { 50% { background-color: #f8d7da; } }
</style>
@endpush

---
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    const getCsrfToken = () => { return document.querySelector('meta[name="csrf-token"]')?.content || ''; };

    const showToast = (message, type) => {
        document.querySelectorAll('.kanban-toast').forEach(toast => toast.remove());
        const toast = document.createElement('div');
        toast.className = `kanban-toast ${type}`;
        toast.innerHTML = `<span>${message}</span><button type="button" class="btn-close btn-close-white ms-2" onclick="this.parentElement.remove()"></button>`;
        document.body.appendChild(toast);
        void toast.offsetWidth;
        toast.classList.add('show');
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
    };

    const updateColumnCounters = () => {
        const columns = document.querySelectorAll('.kanban-column');
        columns.forEach(column => {
            const count = column.querySelectorAll('.kanban-card').length;
            const badge = column.closest('.card')?.querySelector('.badge');
            if (badge) badge.textContent = count;
            const emptyPlaceholder = column.querySelector('.kanban-empty-placeholder');
            if (emptyPlaceholder) emptyPlaceholder.style.display = count > 0 ? 'none' : 'block';
        });
    };

    const checkViewMoreButton = () => {
        document.querySelectorAll('.kanban-column').forEach(column => {
            const leads = column.querySelectorAll('.kanban-card');
            const cardCount = leads.length;
            const columnBody = column.closest('.card-body');
            let viewMoreButton = columnBody.querySelector('.view-more-button');

            if (!viewMoreButton) {
                const placeholder = columnBody.querySelector('.view-more-button-placeholder');
                if (!placeholder) return;
                viewMoreButton = document.createElement('div');
                viewMoreButton.className = 'view-more-button';
                viewMoreButton.onclick = function () {
                    const isShowingAll = column.classList.toggle('show-all');
                    this.textContent = isShowingAll ? 'Ver menos cards...' : 'Ver mais cards...';
                };
                placeholder.replaceWith(viewMoreButton);
            }

            if (cardCount > 5) {
                viewMoreButton.classList.add('visible');
                const isShowingAll = column.classList.contains('show-all');
                viewMoreButton.textContent = isShowingAll ? 'Ver menos cards...' : 'Ver mais cards...';
            } else {
                viewMoreButton.classList.remove('visible');
                column.classList.remove('show-all');
            }
        });
    };

    const initializeSortableColumns = () => {
        const columns = document.querySelectorAll('.kanban-column');
        const updateUrl = document.getElementById('kanban-wrapper')?.dataset.updateUrl;

        if (!updateUrl || columns.length === 0) {
            console.warn('Kanban: Elementos ou URL de atualização não encontrados.');
            return;
        }

        columns.forEach(column => {
            let sortableInstance = Sortable.get(column);
            if (sortableInstance) sortableInstance.destroy();

            new Sortable(column, {
                group: 'kanban-leads',
                animation: 150,
                draggable: '.kanban-card',
                handle: '.kanban-card',
                ghostClass: 'kanban-ghost',

                onEnd: function (evt) {
                    const leadId = evt.item.dataset.id;
                    const leadItem = evt.item;
                    const oldStatus = leadItem.dataset.status;
                    const newStatus = evt.to.dataset.status;

                    if (oldStatus === newStatus && evt.oldIndex === evt.newIndex) return;

                    leadItem.classList.add('kanban-loading');

                    const toColumn = evt.to;
                    const itemsInColumn = Array.from(toColumn.querySelectorAll('.kanban-card'));
                    const newOrder = itemsInColumn.findIndex(item => item.dataset.id === leadId);

                    leadItem.dataset.status = newStatus;
                    leadItem.dataset.order = newOrder;

                    const columnOrderPayload = itemsInColumn.map((item, index) => ({
                        id: item.dataset.id,
                        order: index
                    }));

                    fetch(updateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            id: leadId,
                            status: newStatus,
                            order: newOrder,
                            column_order: columnOrderPayload
                        }),
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(`Status de Rede: ${response.status} ${response.statusText}`);
                        return response.json();
                    })
                    .then(data => {
                        leadItem.classList.remove('kanban-loading');
                        if (data.success) {
                            leadItem.classList.add('kanban-blink-success');
                            showToast('Lead movido com sucesso!', 'success');
                            updateColumnCounters();
                            checkViewMoreButton();
                            setTimeout(() => leadItem.classList.remove('kanban-blink-success'), 700);
                        } else {
                            leadItem.classList.add('kanban-blink-danger');
                            showToast('Erro: ' + (data.message || 'Falha ao atualizar lead.'), 'danger');
                            setTimeout(() => leadItem.classList.remove('kanban-blink-danger'), 700);
                        }
                    })
                    .catch(error => {
                        leadItem.classList.remove('kanban-loading');
                        let displayMessage = 'Erro: ' + error.message;
                        showToast(displayMessage, 'danger');
                        setTimeout(() => location.reload(), 1500);
                    });
                }
            });
        });

        updateColumnCounters();
        checkViewMoreButton();
    };

    document.addEventListener('DOMContentLoaded', initializeSortableColumns);
    document.addEventListener('screen:load', initializeSortableColumns);
    document.addEventListener('screen:reload', initializeSortableColumns);

    // BOTÃO DE REFRESH
    document.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'kanban-refresh-btn') {
            window.location.reload();
        }
    });
</script>
@endpush
