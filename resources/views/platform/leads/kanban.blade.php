@php
/**
 * resources/views/platform/leads/kanban.blade.php
 * Kanban completo com:
 * - IA integrada e 100% funcional (Geração + Cópia)
 * - Cores de Status personalizadas
 * - Drag-and-Drop otimizado com Sortable.js
 * - Contadores de Leads por coluna
 */
@endphp

{{-- Botão de Atualização --}}
<div class="d-flex justify-content-end mb-2 mt-3">
    <button id="kanban-refresh-btn" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-arrow-clockwise me-1"></i> Atualizar Kanban
    </button>
</div>

{{-- KANBAN --}}
<div class="container-fluid mt-3">
    <div id="kanban-wrapper"
         data-update-url="{{ route('platform.leads.kanban.update') }}"
         data-ai-url="{{ route('platform.leads.ai.followup') }}"
         class="row g-4">

        @foreach ($columns as $column)
            @php $statusSlug = \Illuminate\Support\Str::slug(strtolower($column->name)); @endphp

            <div class="col-12 col-md-4 status-{{ $statusSlug }}">
                <div class="card shadow-sm border-0 h-100">

                    {{-- Cabeçalho --}}
                    {{-- O header-bg-color será definido no CSS para manter a cor por status --}}
                    <div class="card-header d-flex justify-content-between align-items-center header-bg-color">
                        <strong>{{ $column->name }}</strong>
                        <span class="badge bg-light text-dark">{{ $column->leads->count() }}</span>
                    </div>

                    {{-- Cards Container --}}
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
                                               class="text-decoration-none text-dark">
                                                {{ $lead->nome ?? $lead->name ?? 'Lead #' . $lead->id }}
                                            </a>
                                        </h6>

                                        <small class="text-muted d-block">
                                            Corretor: {{ optional($lead->corretor)->nome ?? 'Não atribuído' }}
                                        </small>

                                        @if ($lead->propostas?->count())
                                            <span class="badge bg-info mt-1">{{ $lead->propostas->count() }} Proposta(s)</span>
                                        @endif
                                        @if ($lead->contratos?->count())
                                            <span class="badge bg-success mt-1 ms-1">{{ $lead->contratos->count() }} Contrato(s)</span>
                                        @endif
                                    </div>

                                    <button class="btn btn-sm btn-outline-secondary ai-icon border-0"
                                            data-lead-id="{{ $lead->id }}"
                                            data-lead-name="{{ $lead->nome ?? $lead->name ?? 'Lead #' . $lead->id }}"
                                            data-lead-status="{{ $column->status }}"
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
                {{-- Loading --}}
                <div id="ai-loading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Gerando sugestão com IA...</p>
                </div>

                {{-- Ganchos --}}
                <div id="ai-hooks" class="mb-4">
                    <h6 class="fw-bold text-muted mb-3">
                        <i class="bi bi-lightbulb me-1"></i> Ganchos para Engajar:
                    </h6>
                    <div class="d-flex flex-wrap gap-2" id="hooks-container"></div>

                    <button type="button" id="generate-ai-btn" class="btn btn-primary mt-3">
                        <i class="bi bi-stars me-1"></i> Gerar Sugestão com IA
                    </button>
                </div>

                {{-- Mensagem Gerada --}}
                <div id="generated-message-container" class="border rounded p-3 bg-light d-none">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong>Sugestão Gerada:</strong>
                        <button id="copy-message-btn" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-copy"></i> Copiar
                        </button>
                    </div>
                    <pre id="generated-message" class="mb-0 small text-dark"></pre>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>

        </div>
    </div>
</div>

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* KANBAN */
.kanban-column {
    background: #f8f9fa;
    min-height: 200px;
    padding: 10px;
    border-radius: 8px;
}
.kanban-card {
    cursor: grab;
    transition: box-shadow .2s;
}
.kanban-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.kanban-ghost {
    opacity: .4!important;
    border: 2px dashed #aaa!important;
    background: #ddd;
}

/* Status Colors (Header) */
.status-novo .card-header { background:#007bff;color:#fff; } /* Primary */
.status-qualificacao .card-header { background:#ffc107;color:#333; } /* Warning */
.status-visita .card-header { background:#28a745;color:#fff; } /* Success */
.status-negociacao .card-header { background:#fd7e14;color:#fff; } /* Orange/Dark Warning */
.status-fechamento .card-header { background:#6f42c1;color:#fff; } /* Purple/Indigo */
.status-perdido .card-header { background:#dc3545;color:#fff; } /* Danger */

/* Hooks */
.hook-chip {
    padding: .5rem 1rem;
    border-radius: 50px;
    background:#e3f2fd;
    border:1px solid #2196f3;
    color:#1976d2;
    cursor:pointer;
    transition:all .2s;
    font-size: 0.9rem;
}
.hook-chip:hover, .hook-chip.selected {
    background:#2196f3;
    color:white;
}

/* Toast */
.kanban-toast {
    position:fixed;
    top:20px;
    right:20px;
    padding:12px 20px;
    border-radius:8px;
    z-index:10000;
    opacity:0;
    transition:opacity .3s;
    color:white;
    font-weight: 500;
}
.kanban-toast.show { opacity:1; }
.kanban-toast.success { background:#28a745; }
.kanban-toast.danger { background:#dc3545; }
.kanban-toast.warning { background:#ffc107;color:#212529; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ==========================================
    CONFIGURAÇÃO GLOBAL E HELPERS
========================================== */
const KANBAN_WRAPPER = document.getElementById('kanban-wrapper');
const AI_URL = KANBAN_WRAPPER.dataset.aiUrl;
const UPDATE_URL = KANBAN_WRAPPER.dataset.updateUrl;
let currentLead = null; // Armazena dados do lead ativo no modal

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

const showToast = (msg, type='success') => {
    document.querySelectorAll('.kanban-toast').forEach(t => t.remove());
    const toast = document.createElement('div');
    toast.className = `kanban-toast ${type}`;
    toast.innerHTML = `<span>${msg}</span>`;
    document.body.appendChild(toast);
    void toast.offsetWidth; // Trigger reflow
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

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

function getHooksForStatus(status) {
    return hooksByStatus[status] || hooksByStatus.novo;
}

function shuffle(arr) {
    return arr.sort(() => Math.random() - 0.5);
}

/* ==========================================
    DRAG & DROP + CONTADORES
========================================== */
function initializeKanban() {
    document.querySelectorAll('.kanban-column').forEach(col => {
        // Destrói instância existente para evitar duplicidade em recarregamentos dinâmicos
        const existing = Sortable.get(col);
        if (existing) existing.destroy();

        new Sortable(col, {
            group: 'kanban-leads',
            animation: 150,
            draggable: '.kanban-card',
            filter: '.ai-icon',
            preventOnFilter: true,
            ghostClass: 'kanban-ghost',

            onEnd: async (evt) => {
                const card = evt.item;
                const newStatus = evt.to.dataset.status;

                // Atualiza o status no DOM e no objeto do lead atual (se for o mesmo)
                card.dataset.status = newStatus;
                const aiBtn = card.querySelector('.ai-icon');
                if (aiBtn) aiBtn.dataset.leadStatus = newStatus;

                if (currentLead && currentLead.id === card.dataset.id) {
                    currentLead.status = newStatus;
                }

                // Calcula a nova ordem
                const order = Array.from(evt.to.querySelectorAll('.kanban-card'))
                    .findIndex(c => c.dataset.id === card.dataset.id);

                try {
                    const response = await fetch(UPDATE_URL, {
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
                    });

                    const res = await response.json();

                    if (res.success) {
                        showToast('Lead movido com sucesso!', 'success');
                        updateColumnCounters();
                    } else {
                        showToast('Erro ao mover lead: ' + (res.message || 'Erro desconhecido'), 'danger');
                    }
                } catch (err) {
                    showToast('Erro de conexão ao mover lead.', 'danger');
                }
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

    currentLead = {
        id: btn.dataset.leadId,
        name: btn.dataset.leadName,
        status: btn.dataset.leadStatus
    };

    document.getElementById('modal-lead-name').textContent = currentLead.name;
    document.getElementById('ai-loading').classList.add('d-none');
    document.getElementById('generated-message-container').classList.add('d-none');

    // Popular Ganchos
    const hooks = getHooksForStatus(currentLead.status);
    const randomHooks = shuffle([...hooks]).slice(0, 5); // Exibe 5 ganchos aleatórios

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

/* ==========================================
    GERAR E COPIAR IA
========================================== */
document.getElementById('generate-ai-btn').addEventListener('click', async () => {
    if (!currentLead) return showToast('Erro interno: lead não encontrado.', 'danger');

    const selectedHooks = [...document.querySelectorAll('#hooks-container .hook-chip.selected')]
        .map(c => c.textContent)
        .join(' | ');

    if (!selectedHooks) {
        return showToast('Selecione pelo menos um gancho!', 'warning');
    }

    const loading = document.getElementById('ai-loading');
    loading.classList.remove('d-none');
    document.getElementById('generated-message-container').classList.add('d-none');

    try {
        const res = await fetch(AI_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                lead_id: currentLead.id,
                status: currentLead.status, // Enviar status para o backend da IA
                contexto_extra: selectedHooks
            })
        });

        const data = await res.json();

        if (data.success && data.content) {
            document.getElementById('generated-message').textContent = data.content;
            document.getElementById('generated-message-container').classList.remove('d-none');
            showToast('Sugestão gerada com sucesso!', 'success');
        } else {
            throw new Error(data.message || data.error || 'Resposta inválida da IA');
        }
    } catch (err) {
        console.error('Erro IA:', err);
        showToast('Erro ao gerar mensagem com IA: ' + err.message, 'danger');
    } finally {
        loading.classList.add('d-none');
    }
});

// Copiar mensagem
document.getElementById('copy-message-btn')?.addEventListener('click', () => {
    const text = document.getElementById('generated-message').textContent;
    navigator.clipboard.writeText(text).then(() => {
        showToast('Mensagem copiada para a área de transferência!', 'success');
    }).catch(err => {
        console.error('Erro ao copiar:', err);
        showToast('Falha ao copiar mensagem. Tente manualmente.', 'warning');
    });
});

/* ==========================================
    INIT
========================================== */
document.addEventListener('DOMContentLoaded', initializeKanban);
document.addEventListener('screen:load', initializeKanban);
document.addEventListener('screen:reload', initializeKanban);

document.getElementById('kanban-refresh-btn').onclick = () => window.location.reload();
</script>
@endpush