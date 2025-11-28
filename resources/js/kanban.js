import Sortable from 'sortablejs';

// =============================================
// FUNÇÕES AUXILIARES DO KANBAN E GERAIS
// =============================================

const updateLeadStatus = async (url, token, leadId, status, order) => {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ id: leadId, status, order }),
        });

        if (!response.ok) {
            console.warn(`[Kanban] Requisição falhou: ${response.status} ${response.statusText}`);
            return false;
        }

        const data = await response.json();
        return !!data.success;
    } catch (error) {
        console.error('[Kanban] Erro na requisição AJAX:', error);
        throw error;
    }
};

const handleMoveSuccess = (card, newStatus) => {
    card.dataset.status = newStatus;
    showToast('Lead movido com sucesso!', 'success');
    animateCard(card, 'success');
};

const handleMoveError = (card, oldColumn, oldIndex, message) => {
    if (oldColumn && oldIndex != null) {
        oldColumn.insertBefore(card, oldColumn.children[oldIndex] || null);
    }
    showToast(message, 'danger');
    animateCard(card, 'danger');
};

const showLoader = (card) => {
    card.classList.add('kanban-loading');
    if (!card.querySelector('.kanban-loader')) {
        const loader = document.createElement('div');
        loader.className = 'kanban-loader';
        loader.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';
        card.appendChild(loader);
    }
};

const hideLoader = (card) => {
    card.classList.remove('kanban-loading');
    const loader = card.querySelector('.kanban-loader');
    if (loader) loader.remove();
};

const updateColumnCounters = (columns) => {
    columns.forEach(column => {
        const count = column.querySelectorAll('.kanban-card').length;
        const badge = column.closest('.card')?.querySelector('.badge');
        if (badge) badge.textContent = count.toString();
    });
};

const animateCard = (card, type) => {
    const className = type === 'success' ? 'kanban-blink-success' : 'kanban-blink-danger';
    card.classList.add(className);
    setTimeout(() => card.classList.remove(className), 700);
};

const showToast = (message, type = 'info') => {
    document.querySelectorAll('.kanban-toast').forEach(toast => toast.remove());

    const toast = document.createElement('div');
    toast.className = `kanban-toast ${type}`;
    toast.innerHTML = `
        <span>${message}</span>
        <button type="button" class="btn-close btn-close-white ms-2" onclick="this.parentElement.remove()"></button>
    `;
    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('show'));

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
};

/**
 * Função auxiliar para extrair frases com interrogação (ganchos)
 * @param {string} text
 * @returns {string[]}
 */
function extractHooks(text) {
    const sentences = text.split(/[.!?]/).map(s => s.trim()).filter(Boolean);
    return sentences.filter(s => s.includes('?') && s.length < 80).slice(0, 4);
}

// =============================================
// FUNÇÃO PARA GERAR SUGESTÃO COM IA (GLOBAL)
// =============================================
window.generateAiSuggestion = async (leadId) => {
    // Nota: O Código 2 usa 'event.target' que é global no contexto de um 'onclick'.
    // Mantive a estrutura original, mas o 'button' não é usado dentro da função.
    // const button = event.target; 

    const modal = document.getElementById('ai-followup-modal');
    const body = modal.querySelector('.modal-body');
    const loader = modal.querySelector('.ai-loader');
    const content = modal.querySelector('.ai-content');
    const hooks = modal.querySelector('.ai-hooks');

    // Mostra loader
    loader.classList.remove('d-none');
    content.classList.add('d-none');
    hooks.innerHTML = '';
    body.querySelector('h5').textContent = 'Gerando sugestão com IA...';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!csrfToken) {
            throw new Error("CSRF token não encontrado.");
        }

        const response = await fetch('/admin/leads/ai/followup', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ lead_id: leadId })
        });

        const data = await response.json();

        if (data.success && data.content) {
            body.querySelector('h5').textContent = 'Follow-up com IA – Pronto!';
            content.textContent = data.content;
            content.classList.remove('d-none');

            // Gera ganchos rápidos
            const hooksArray = extractHooks(data.content);
            hooksArray.forEach(hook => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-primary btn-sm me-2 mb-2';
                btn.textContent = hook;
                btn.onclick = () => navigator.clipboard.writeText(hook).then(() => showToast('Gancho copiado!', 'success'));
                hooks.appendChild(btn);
            });
        } else {
            throw new Error(data.error || 'Resposta inválida da IA');
        }
    } catch (err) {
        console.error('[AI] Erro:', err);
        body.querySelector('h5').textContent = 'Erro ao gerar sugestão';
        content.textContent = err.message.includes('CSRF') 
            ? 'Erro de segurança: Token CSRF ausente.' 
            : 'Não foi possível gerar a mensagem. Tente novamente mais tarde.';
        content.classList.remove('d-none');
    } finally {
        loader.classList.add('d-none');
    }
};

// =============================================
// INICIALIZAÇÃO DO KANBAN (DOM Content Loaded)
// =============================================

const initializeKanban = () => {
    document.addEventListener('DOMContentLoaded', () => {

        if (!window.Sortable) {
            console.error('[Kanban] SortableJS não encontrado. Verifique a importação.');
            return;
        }

        const wrapper = document.getElementById('kanban-wrapper');
        if (!wrapper) {
            console.warn('[Kanban] Elemento #kanban-wrapper não encontrado.');
            return;
        }

        const updateUrl = wrapper.dataset.updateUrl;
        if (!updateUrl) {
            console.error('[Kanban] Atributo data-update-url ausente no #kanban-wrapper.');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || window.Laravel?.csrfToken;
        if (!csrfToken) {
            console.error('[Kanban] CSRF token não encontrado. Adicione <meta name="csrf-token" content="{{ csrf_token() }}"> no layout ou configure window.Laravel.csrfToken.');
            return;
        }

        const columns = document.querySelectorAll('.kanban-column');
        if (!columns.length) {
            console.warn('[Kanban] Nenhuma coluna .kanban-column encontrada.');
            return;
        }

        let isUpdating = false;

        columns.forEach(column => {
            new Sortable(column, {
                group: 'leads-kanban',
                animation: 150,
                ghostClass: 'kanban-ghost',
                chosenClass: 'kanban-chosen',
                dragClass: 'kanban-dragging',
                swapThreshold: 0.6,
                onStart: ({ item }) => {
                    document.body.style.overflow = 'hidden';
                    item.classList.add('dragging');
                },
                onEnd: async ({ item, to, from, oldIndex, newIndex }) => {
                    // Previne duplicação de updates
                    if (isUpdating) return; 
                    isUpdating = true;

                    const leadId = item.dataset.id;
                    const newStatus = to.dataset.status;

                    if (!leadId || !newStatus) {
                        console.error('[Kanban] Atributos data-id ou data-status ausentes no card.');
                        isUpdating = false;
                        return;
                    }

                    showLoader(item);

                    try {
                        const success = await updateLeadStatus(updateUrl, csrfToken, leadId, newStatus, newIndex);
                        if (success) {
                            handleMoveSuccess(item, newStatus);
                        } else {
                            // Se falhar, reverte a UI
                            handleMoveError(item, from, oldIndex, 'Falha ao atualizar o lead.');
                        }
                    } catch (error) {
                        handleMoveError(item, from, oldIndex, 'Erro de comunicação com o servidor.');
                    } finally {
                        isUpdating = false;
                        document.body.style.overflow = '';
                        hideLoader(item);
                        updateColumnCounters(columns);
                    }
                },
            });
        });
    });
};

initializeKanban();