@php
/**
 * resources/views/platform/leads/kanban.blade.php
 * Kanban completo com:
 * - IA integrada
 * - Ganchos otimizados
 * - Status sincronizado no drag-and-drop
 * - Correções de evento
 * - Atualizações defensivas
 */
@endphp

{{-- Botão de Atualização --}}
<div class="d-flex justify-content-end mb-2 mt-3">
    <button id="kanban-refresh-btn" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-arrow-clockwise me-1"></i> Desbloquear Movimentação de Cards
    </button>
</div>

{{-- KANBAN --}}
<div class="container-fluid mt-3">
    <div id="kanban-wrapper"
         data-update-url="{{ route('platform.leads.kanban.update') }}"
         data-ai-url="@if(Route::has('platform.leads.ai.followup')){{ route('platform.leads.ai.followup') }}@else# @endif"
         class="row g-4">

        @foreach ($columns as $column)
            @php
                $statusSlug = \Illuminate\Support\Str::slug(strtolower($column->name));
            @endphp

            <div class="col-12 col-md-4 status-{{ $statusSlug }}">
                <div class="card shadow-sm border-0 h-100">

                    {{-- Cabeçalho --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>{{ $column->name }}</strong>
                        <span class="badge bg-light text-dark">{{ $column->leads->count() }}</span>
                    </div>

                    {{-- Cards --}}
                    <div class="card-body p-2">
                        <div class="kanban-column"
                             data-status="{{ $column->status }}"
                             id="kanban-column-{{ $column->status }}">

                            @forelse ($column->leads as $lead)
                                <div class="kanban-card mb-2 p-3 bg-white border rounded d-flex justify-content-between align-items-start"
                                     data-id="{{ $lead->id }}"
                                     data-status="{{ $column->status }}"
                                     data-order="{{ $lead->order ?? 0 }}"
                                     draggable="true">

                                    <div>
                                        <h6 class="mb-1">
                                            <a href="{{ route('platform.leads.edit', $lead->id) }}"
                                               class="text-decoration-none">
                                                {{ $lead->nome ?? $lead->name ?? 'Lead #' . $lead->id }}
                                            </a>
                                        </h6>

                                        <small class="text-muted d-block">
                                            Corretor: {{ optional($lead->corretor)->nome ?? 'Não atribuído' }}
                                        </small>

                                        @if ($lead->propostas?->count())
                                            <span class="badge bg-info mt-1">
                                                {{ $lead->propostas->count() }} Proposta(s)
                                            </span>
                                        @endif

                                        @if ($lead->contratos?->count())
                                            <span class="badge bg-success mt-1 ms-1">
                                                {{ $lead->contratos->count() }} Contrato(s)
                                            </span>
                                        @endif
                                    </div>

                                    <button class="btn btn-sm btn-outline-secondary ai-icon border-0"
                                            data-lead-id="{{ $lead->id }}"
                                            data-lead-status="{{ $column->status }}"
                                            data-lead-name="{{ $lead->nome ?? $lead->name ?? 'Lead #' . $lead->id }}"
                                            title="Gerar Follow-up com IA">
                                        <i class="bi bi-robot"></i>
                                    </button>

                                </div>
                            @empty
                                <p class="text-muted text-center small kanban-empty-placeholder">
                                    Nenhum lead nesta etapa.
                                </p>
                            @endforelse
                        </div>

                        <div class="view-more-button-placeholder"></div>
                    </div>

                </div>
            </div>
        @endforeach

    </div>
</div>

{{-- MODAL IA --}}
<div class="modal fade" id="aiFollowupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-robot me-2"></i>
                    Follow-up com IA — <span id="modal-lead-name"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div id="ai-message" class="alert alert-info d-none mb-3"></div>

                {{-- Ganchos --}}
                <div id="ai-hooks" class="mb-4">
                    <h6 class="fw-bold text-muted mb-2">
                        <i class="bi bi-lightbulb me-1"></i>Ganchos para Engajar:
                    </h6>
                    <div class="d-flex flex-wrap gap-2" id="hooks-container"></div>

                    <button type="button" id="generate-ai-btn"
                            class="btn btn-outline-primary mt-3">
                        Gerar Sugestão com IA
                    </button>
                </div>

                {{-- Mensagem --}}
                <div id="generated-message-container" class="d-none mb-3">
                    <label class="fw-bold form-label">Sugestão Gerada:</label>
                    <pre id="generated-message" class="bg-light border p-3 rounded small"></pre>
                </div>

                {{-- Histórico --}}
                <div id="ai-history" class="mt-4">
                    <h6 class="fw-bold">
                        <i class="bi bi-clock-history me-1"></i> Histórico
                    </h6>
                    <div class="timeline"></div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button id="send-message-btn"
                        class="btn btn-primary d-none">
                    <i class="bi bi-send me-1"></i>Enviar Mensagem
                </button>
            </div>

        </div>
    </div>
</div>

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
/* KANBAN */
.kanban-column {
    background: #f0f0f0;
    min-height: 200px;
    padding: 10px;
    border-radius: 4px;
}
.kanban-card {
    cursor: grab;
    transition: box-shadow .2s;
}
.kanban-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,.1);
}
.kanban-ghost {
    opacity: .4!important;
    border: 2px dashed #aaa!important;
}

/* Status Colors */
.status-novo .card-header { background:#007bff;color:#fff; }
.status-qualificacao .card-header { background:#ffc107;color:#333; }
.status-visita .card-header { background:#28a745;color:#fff; }
.status-negociacao .card-header { background:#fd7e14;color:#fff; }
.status-fechamento .card-header { background:#6f42c1;color:#fff; }
.status-perdido .card-header { background:#dc3545;color:#fff; }

/* Hooks */
.hook-chip {
    padding: .5rem 1rem;
    border-radius: 20px;
    background:#e3f2fd;
    border:1px solid #2196f3;
    color:#1976d2;
    cursor:pointer;
    transition:all .2s;
}
.hook-chip:hover {
    background:#2196f3;
    color:white;
}
.hook-chip.selected {
    background:#2196f3;
    color:white;
}

/* Timeline */
.timeline {
    position:relative;
    padding-left: 30px;
    border-left:3px solid #dee2e6;
}
.timeline-item {
    margin-bottom:20px;
    padding-left:20px;
}
.timeline-item::before {
    content:'';
    position:absolute;
    left:-18px;
    width:16px;
    height:16px;
    background:#007bff;
    border-radius:50%;
    border:3px solid white;
}

/* Toast */
.kanban-toast {
    position:fixed;
    top:15px;
    right:15px;
    padding:10px 20px;
    border-radius:5px;
    z-index:10000;
    opacity:0;
    transition:opacity .3s;
    color:white;
}
.kanban-toast.show { opacity:1; }
.kanban-toast.success { background:#28a745; }
.kanban-toast.danger { background:#dc3545; }
.kanban-toast.warning { background:#ffc107;color:#333; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ==========================================
   HELPERS
========================================== */
const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

const showToast = (msg, type='success') => {
    document.querySelectorAll('.kanban-toast').forEach(t => t.remove());
    const toast = document.createElement('div');
    toast.className = `kanban-toast ${type}`;
    toast.innerHTML = msg;
    document.body.appendChild(toast);
    void toast.offsetWidth;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

/* ==========================================
   GANCHOS POR STATUS
========================================== */
const hooksByStatus = {
    novo: [
        'Qual o valor ideal que você imagina investir?',
        'Posso te ligar rapidinho? 2 min.',
        'Você já tem região preferida?',
        'Quer ver opções agora?',
        'Prefere casa ou apartamento?',
        'Quer simular entrada/parcelas?',
        'Quer opções ainda hoje?',
        'Você já mora na região?',
        'O que não pode faltar no imóvel?',
        'Quer começar pela localização?'
    ],
    qualificacao: [
        'Quer fazer análise gratuita de crédito?',
        'Posso simular suas parcelas?',
        'Quanto imagina de entrada?',
        'Quer ver condições com bancos?',
        'Qual faixa de valor te conforta?',
        'Quer comparar parcelas?',
        'Prefere entrada menor ou parcelas menores?'
    ],
    visita: [
        'Qual horário fica melhor pra visitar?',
        'Quer ver mais opções antes?',
        'Ficou alguma dúvida?',
        'Visita amanhã ou sábado?',
        'Quer fotos/vídeos do imóvel?',
        'O que achou da localização?',
        'Posso agendar agora?'
    ],
    negociacao: [
        'Qual valor imaginou na proposta?',
        'Posso ajustar parcelas pra você?',
        'Quer montar a proposta?',
        'Prefere entrada menor?',
        'Se o construtor cobrir, você fecha?',
        'Quer tentar desconto especial?'
    ],
    fechamento: [
        'Ficou dúvida sobre taxas/docs?',
        'Quer revisar antes da assinatura?',
        'Te explico ITBI rapidinho?',
        'Avançamos pra assinatura?',
        'Assinatura digital ou presencial?',
        'Qual horário pra assinar?'
    ],
    perdido: [
        'Quer ver opções novas que chegaram?',
        'Seus planos mudaram?',
        'Te mando novidades da semana?',
        'Quer opções mais baratas?',
        'Quer tentar reduzir entrada?',
        'Te envio imóveis parecidos?'
    ]
};

/* ==========================================
   DRAG & DROP
========================================== */
function initializeSortableColumns() {
    const columns = document.querySelectorAll('.kanban-column');
    const updateUrl = document.getElementById('kanban-wrapper').dataset.updateUrl;

    columns.forEach(column => {
        const existing = Sortable.get(column);
        if (existing) existing.destroy();

        new Sortable(column, {
            group: 'kanban-leads',
            animation: 150,
            draggable: '.kanban-card',
            filter: '.ai-icon',
            preventOnFilter: true,
            ghostClass: 'kanban-ghost',

            onEnd: evt => {
                const card = evt.item;
                const newStatus = evt.to.dataset.status;
                const oldStatus = card.dataset.status;

                if (newStatus === oldStatus && evt.oldIndex === evt.newIndex) return;

                card.dataset.status = newStatus;

                const aiBtn = card.querySelector('.ai-icon');
                if (aiBtn) aiBtn.dataset.leadStatus = newStatus;

                if (window.currentLeadData &&
                    window.currentLeadData.id === card.dataset.id) {
                    window.currentLeadData.status = newStatus;
                }

                const cards = [...evt.to.querySelectorAll('.kanban-card')];
                const order = cards.findIndex(c => c.dataset.id === card.dataset.id);

                fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id: card.dataset.id,
                        status: newStatus,
                        order: order
                    })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        showToast('Lead movido!', 'success');
                        updateColumnCounters();
                    } else {
                        showToast('Erro ao mover lead: ' + res.message, 'danger');
                    }
                })
                .catch(err => {
                    showToast('Erro: ' + err.message, 'danger');
                });
            }
        });
    });

    updateColumnCounters();
}

function updateColumnCounters() {
    document.querySelectorAll('.kanban-column').forEach(col => {
        const count = col.querySelectorAll('.kanban-card').length;
        const badge = col.closest('.card')?.querySelector('.badge');
        if (badge) badge.textContent = count;
    });
}

/* ==========================================
   ABRIR MODAL IA
========================================== */
document.addEventListener('click', e => {
    const btn = e.target.closest('.ai-icon');
    if (!btn) return;

    const leadId = btn.dataset.leadId;
    const leadName = btn.dataset.leadName;
    const leadStatus = btn.dataset.leadStatus;

    window.currentLeadData = { id: leadId, name: leadName, status: leadStatus };

    document.getElementById('modal-lead-name').textContent = leadName;

    document.getElementById('ai-message').classList.add('d-none');
    document.getElementById('generated-message-container').classList.add('d-none');
    document.getElementById('send-message-btn').classList.add('d-none');

    const hooks = hooksByStatus[leadStatus] ?? hooksByStatus['novo'];
    const randomHooks = shuffle([...hooks]).slice(0, 4);

    const container = document.getElementById('hooks-container');
    container.innerHTML = '';

    randomHooks.forEach(text => {
        const chip = document.createElement('span');
        chip.className = 'hook-chip';
        chip.textContent = text;
        chip.onclick = () => chip.classList.toggle('selected');
        container.appendChild(chip);
    });

    bootstrap.Modal.getOrCreateInstance(document.getElementById('aiFollowupModal')).show();
});

function shuffle(arr) {
    return arr.sort(() => Math.random() - 0.5);
}

/* ==========================================
   GERAR IA
========================================== */
document.getElementById('generate-ai-btn').addEventListener('click', () => {
    const selectedHooks = [...document.querySelectorAll('.hook-chip.selected')].map(c => c.textContent);

    if (!selectedHooks.length) return showToast('Selecione pelo menos um gancho!', 'warning');

    if (!window.currentLeadData) return showToast('Erro interno: lead não encontrado.', 'danger');

    const aiUrl = document.getElementById('kanban-wrapper').dataset.aiUrl;

    const loading = document.getElementById('ai-message');
    loading.classList.remove('d-none');
    loading.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary"></div>
            Gerando sugestão...
        </div>
    `;

    fetch(aiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            lead_id: window.currentLeadData.id,
            status: window.currentLeadData.status,
            contexto_extra: selectedHooks.join(' | ')
        })
    })
    .then(r => r.json())
    .then(res => {
        loading.classList.add('d-none');

        if (!res.success) {
            loading.classList.remove('d-none');
            loading.innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
            return;
        }

        document.getElementById('generated-message-container').classList.remove('d-none');
        document.getElementById('generated-message').textContent = res.message;
        document.getElementById('send-message-btn').classList.remove('d-none');

        showToast('Sugestão gerada!', 'success');
    })
    .catch(err => {
        loading.classList.remove('d-none');
        loading.innerHTML = `<div class="alert alert-danger">Erro: ${err.message}</div>`;
    });
});

/* ==========================================
   ENVIAR MENSAGEM
========================================== */
document.getElementById('send-message-btn').addEventListener('click', () => {
    showToast('Mensagem enviada! (Simulação)', 'success');
    bootstrap.Modal.getInstance(document.getElementById('aiFollowupModal')).hide();
});

/* ==========================================
   INIT
========================================== */
document.addEventListener('DOMContentLoaded', initializeSortableColumns);
document.addEventListener('screen:load', initializeSortableColumns);
document.addEventListener('screen:reload', initializeSortableColumns);

document.getElementById('kanban-refresh-btn').onclick = () => window.location.reload();
</script>
@endpush
