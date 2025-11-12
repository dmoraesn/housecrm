<!-- CDN Feather Icons -->
<script src="https://unpkg.com/feather-icons"></script>

<style>
    .floating-calc-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 12px 16px;
        z-index: 1050;
        width: 260px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    .floating-calc-notification.minimized {
        width: 180px;
        height: 48px;
        overflow: hidden;
        cursor: pointer;
    }

    .saldo-principal {
        display: block;
        font-size: 1.3rem;
        font-weight: 600;
        margin-top: 5px;
        margin-bottom: 5px;
    }

    .readonly-highlight {
        background-color: #f8fafc !important;
        font-weight: 500;
    }

    .badge-light {
        background-color: #eef2ff;
        color: #4338ca;
        font-size: 0.7rem;
        padding: 3px 7px;
        border-radius: 0.375rem;
    }
</style>

<div id="calcNotification" class="floating-calc-notification">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 text-secondary">Saldo Restante da Entrada</h6>
        <button id="toggleCalcButton" type="button" class="btn btn-sm btn-outline-secondary" title="Minimizar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path id="iconMinimize" d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z" />
            </svg>
        </button>
    </div>

    <div id="calcDetails">
        <span id="saldoRestanteTopo" class="saldo-principal text-primary">R$ 0,00</span>

        <div class="small text-muted">
            Dif. Avaliação:
            <span class="badge-light" id="difAvaliacao">R$ 0,00</span>
        </div>
        <div class="small text-muted mt-1">
            Financ. Corrigido:
            <span class="badge-light text-success" id="finCorrigido">R$ 0,00</span>
        </div>

        <div class="text-muted small mt-2">Atualizado em tempo real.</div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        feather.replace(); // Renderiza os SVGs
    });
</script>
