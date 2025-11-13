<script src="https://unpkg.com/feather-icons"></script>

<style>
    .floating-calc-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 12px 16px;
        z-index: 1050;
        width: 260px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease-in-out;
    }

    .floating-calc-notification.minimized {
        width: 180px;
        height: 48px;
        overflow: hidden;
        cursor: pointer;
    }

    .saldo-principal {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        margin: 5px 0;
    }

    .badge-light {
        background-color: #eef2ff;
        color: #4338ca;
        font-size: 0.75rem;
        padding: 3px 7px;
        border-radius: 6px;
    }

    .btn-minimize {
        border: none;
        background: none;
        color: #555;
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
    }

    .btn-minimize:hover {
        background-color: #f3f4f6;
    }
</style>

<div id="calcNotification" class="floating-calc-notification">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 text-secondary">Saldo Restante da Entrada</h6>
        <button id="toggleCalcButton" type="button" class="btn-minimize" title="Minimizar">
            <i data-feather="chevron-down"></i>
        </button>
    </div>

    <div id="calcDetails">
        <span id="saldoRestanteTopo" class="saldo-principal text-primary">R$ 0,00</span>
        <div class="small text-muted">Dif. Avaliação:
            <span id="difAvaliacao" class="badge-light">R$ 0,00</span>
        </div>
        <div class="small text-muted mt-1">Financ. Corrigido:
            <span id="finCorrigido" class="badge-light text-success">R$ 0,00</span>
        </div>
        <div class="text-muted small mt-2">Atualizado em tempo real.</div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    feather.replace();

    const notification = document.getElementById('calcNotification');
    const toggleButton = document.getElementById('toggleCalcButton');
    const details = document.getElementById('calcDetails');
    const icon = toggleButton.querySelector('i');
    const saldoTopo = document.getElementById('saldoRestanteTopo');

    let isMinimized = false;

    const toggleMinimize = () => {
        isMinimized = !isMinimized;
        notification.classList.toggle('minimized', isMinimized);
        details.style.display = isMinimized ? 'none' : 'block';
        icon.setAttribute('data-feather', isMinimized ? 'chevron-up' : 'chevron-down');
        feather.replace();
        toggleButton.title = isMinimized ? "Maximizar" : "Minimizar";
    };

    toggleButton.addEventListener('click', toggleMinimize);
    saldoTopo.addEventListener('click', () => { if (isMinimized) toggleMinimize(); });
});
</script>
