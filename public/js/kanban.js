/**
 * public/js/kanban.js
 * Kanban de Leads — Drag & Drop com SortableJS + AJAX + Feedback Visual
 *
 * Compatível com Laravel Orchid (usa Turbo).
 * Inclui animações, feedbacks visuais e proteção CSRF.
 */

document.addEventListener('turbo:load', () => {
    if (typeof Sortable === 'undefined') {
        console.error('[KANBAN] SortableJS não encontrado.');
        return;
    }

    const wrapper = document.getElementById('kanban-wrapper');
    if (!wrapper) {
        console.warn('[KANBAN] Elemento #kanban-wrapper não encontrado.');
        return;
    }

    const updateUrl = wrapper.dataset.updateUrl;
    if (!updateUrl) {
        console.error('[KANBAN] "data-update-url" ausente no wrapper.');
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!token) {
        console.error('[KANBAN] CSRF token não encontrado. Adicione: <meta name="csrf-token" content="{{ csrf_token() }}">');
        return;
    }

    const columns = document.querySelectorAll('.kanban-column');
    if (!columns.length) {
        console.warn('[KANBAN] Nenhuma coluna .kanban-column encontrada.');
        return;
    }

    let isUpdating = false;

    // Inicializa SortableJS para cada coluna
    columns.forEach(column => {
        new Sortable(column, {
            group: 'leads-kanban',
            animation: 150,
            ghostClass: 'kanban-ghost',
            chosenClass: 'kanban-chosen',
            dragClass: 'kanban-dragging',
            swapThreshold: 0.6,
            onStart: e => {
                document.body.style.overflow = 'hidden';
                e.item.classList.add('dragging');
            },
            onEnd: async e => {
                if (isUpdating) return;
                isUpdating = true;

                const card = e.item;
                const leadId = card.dataset.id;
                const newStatus = e.to.dataset.status;
                const oldColumn = e.from;
                const oldIndex = e.oldIndex;

                if (!leadId || !newStatus) {
                    console.error('[KANBAN] data-id ou data-status ausente.');
                    isUpdating = false;
                    return;
                }

                showLoader(card);

                try {
                    const ok = await updateLead(updateUrl, token, leadId, newStatus, e.newIndex);
                    if (ok) handleSuccess(card, newStatus);
                    else handleError(card, oldColumn, oldIndex, 'Falha ao atualizar lead.');
                } catch (err) {
                    handleError(card, oldColumn, oldIndex, 'Erro de comunicação com o servidor.');
                } finally {
                    isUpdating = false;
                    document.body.style.overflow = '';
                    hideLoader(card);
                    refreshCounters();
                }
            }
        });
    });

    /**
     * Envia atualização via AJAX
     */
    async function updateLead(url, token, id, status, order) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id, status, order })
        });

        if (!response.ok) return false;

        const data = await response.json();
        return !!data.success;
    }

    /**
     * Sucesso ao mover lead
     */
    function handleSuccess(card, newStatus) {
        card.dataset.status = newStatus;
        flash('Lead movido com sucesso!', 'success');
        animate(card, 'success');
    }

    /**
     * Erro ao mover lead (reverte)
     */
    function handleError(card, oldColumn, oldIndex, message) {
        if (oldColumn && oldIndex != null) {
            oldColumn.insertBefore(card, oldColumn.children[oldIndex] || null);
        }
        flash(message, 'danger');
        animate(card, 'danger');
    }

    /**
     * Mostra o loader no card
     */
    function showLoader(card) {
        card.classList.add('kanban-loading');
        if (!card.querySelector('.kanban-loader')) {
            const loader = document.createElement('div');
            loader.className = 'kanban-loader';
            loader.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';
            card.appendChild(loader);
        }
    }

    /**
     * Remove o loader do card
     */
    function hideLoader(card) {
        card.classList.remove('kanban-loading');
        const loader = card.querySelector('.kanban-loader');
        if (loader) loader.remove();
    }

    /**
     * Atualiza contadores nas colunas
     */
    function refreshCounters() {
        columns.forEach(col => {
            const count = col.querySelectorAll('.kanban-card').length;
            const badge = col.closest('.card')?.querySelector('.badge');
            if (badge) badge.textContent = count;
        });
    }

    /**
     * Anima visualmente o card
     */
    function animate(card, type) {
        const className = type === 'success' ? 'kanban-blink-success' : 'kanban-blink-danger';
        card.classList.add(className);
        setTimeout(() => card.classList.remove(className), 700);
    }

    /**
     * Toast temporário
     */
    function flash(msg, type = 'info') {
        document.querySelectorAll('.kanban-toast').forEach(el => el.remove());

        const toast = document.createElement('div');
        toast.className = `kanban-toast ${type}`;
        toast.innerHTML = `
            <span>${msg}</span>
            <button type="button" class="btn-close btn-close-white ms-2" onclick="this.parentElement.remove()"></button>
        `;
        document.body.appendChild(toast);

        requestAnimationFrame(() => toast.classList.add('show'));

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
});
