@php
/** @var array $stages */
/** @var \Illuminate\Support\Collection[] $leads */
@endphp

<div class="p-4">
    {{-- Cabeçalho (Reintroduzido do Código 1 com rota corrigida) --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1">Leads</h2>
            <p class="text-muted mb-0">Funil de Vendas</p>
        </div>
        <a href="{{ route('platform.leads.create') }}" class="btn btn-primary">
            <x-orchid-icon path="bs.plus-circle" class="me-1" />
            Novo Lead
        </a>
    </div>
</div>

{{-- Container Kanban (Estrutura Tailwind do Código 2) --}}
<div class="kanban-board grid grid-cols-5 gap-4 p-4">

    @foreach ($stages as $status => $label)
        <div class="kanban-column bg-white rounded-xl shadow p-3 flex flex-col"
             data-status="{{ $status }}"
             style="min-height: 70vh;">

            <div class="flex justify-between items-center mb-3 border-b pb-1">
                <h6 class="text-sm font-semibold text-gray-700">{{ $label }}</h6>
                <span class="lead-count text-xs text-gray-500">
                    ({{ $leads[$status]->count() ?? 0 }} Leads)
                </span>
            </div>

            {{-- Área de Drop --}}
            <div class="kanban-cards flex-1 space-y-2" id="column-{{ $status }}">

                @foreach ($leads[$status] as $lead)
                    <div class="kanban-card bg-gray-50 border border-gray-200 p-3 rounded-lg shadow-sm lead-card"
                         draggable="true"
                         data-id="{{ $lead->id }}"
                         data-edit-url="{{ route('platform.leads.edit', $lead) }}" {{-- Adicionado data-edit-url --}}
                         style="border-left: 3px solid #3b82f6; cursor: pointer;">

                        <div class="font-semibold text-sm text-gray-800">
                            {{ $lead->nome ?? 'Lead #'.$lead->id }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $lead->email ?? '' }}
                        </div>
                        <div class="text-xs mt-1 text-gray-400">
                            Atualizado em {{ $lead->updated_at->format('d/m/Y') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

{{-- Script para D&D e Edição (Refatorado para DOMContentLoaded) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const columns = document.querySelectorAll('.kanban-column');
        const cards = document.querySelectorAll('.lead-card'); // Usando classe refatorada
        let draggedCard = null;

        // --- Funções Auxiliares ---

        function updateCounters() {
            columns.forEach(col => {
                const count = col.querySelectorAll('.lead-card').length;
                const counter = col.querySelector('.lead-count');
                if (counter) counter.textContent = `(${count} Leads)`;
            });
        }

        function saveStatus(leadId, newStatus) {
            // Usando o nome da rota definido no routes/platform.php (leads.updateStatus)
            fetch('{{ route("leads.updateStatus") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ id: leadId, status: newStatus })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    alert('Erro ao salvar mudança de status!');
                }
            })
            .catch(() => alert('Falha na comunicação com o servidor.'));
        }

        // --- Listeners para Cards (Drag e Clique) ---
        cards.forEach(card => {
            // Edição por Clique (do Código 1)
            card.addEventListener('click', e => {
                const editUrl = card.dataset.editUrl;
                if (editUrl) {
                    window.location.href = editUrl;
                }
            });

            // Drag Start (do Código 2, adaptado)
            card.addEventListener('dragstart', e => {
                draggedCard = card;
                e.dataTransfer.effectAllowed = 'move';
                setTimeout(() => card.style.opacity = '0.4', 0);
            });

            // Drag End (do Código 2, adaptado)
            card.addEventListener('dragend', e => {
                card.style.opacity = '1';
                draggedCard = null;
            });
        });

        // --- Listeners para Colunas (Drop) ---
        columns.forEach(col => {
            const columnBody = col.querySelector('.kanban-cards');

            columnBody.addEventListener('dragover', e => {
                e.preventDefault();
                // Adiciona um feedback visual de drag-over
                col.classList.add('border-2', 'border-blue-500', 'border-dashed');
            });

            columnBody.addEventListener('dragleave', e => {
                 // Remove feedback visual
                col.classList.remove('border-2', 'border-blue-500', 'border-dashed');
            });

            columnBody.addEventListener('drop', e => {
                e.preventDefault();
                col.classList.remove('border-2', 'border-blue-500', 'border-dashed');

                if (draggedCard && columnBody) {
                    const newStatus = col.dataset.status;
                    const leadId = draggedCard.dataset.id;

                    columnBody.appendChild(draggedCard);
                    updateCounters();
                    saveStatus(leadId, newStatus);
                }
            });
        });
    });
</script>
