document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("propostaForm");
    if (!form) return;

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    async function calcularBackend() {
        const formData = new FormData(form);
        const proposta = Object.fromEntries(formData.entries());

        try {
            const res = await fetch("/platform/propostas/calculate", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf,
                },
                body: JSON.stringify({ proposta }),
            });

            const data = await res.json();
            atualizarResumo(data);
        } catch (e) {
            console.error("Erro ao calcular:", e);
        }
    }

    function atualizarResumo(data) {
        const el = (id) => document.getElementById(id);
        if (!el("valorEntradaSpan")) return;

        el("valorEntradaSpan").textContent = data.valor_entrada.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
        el("totalParcelamentoSpan").textContent = data.total_parcelamento.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
        el("valorRestanteSpan").textContent = data.valor_restante.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

        const msg = el("validationMessage");
        msg.textContent =
            Math.abs(data.valor_restante) < 0.01
                ? "Plano 100% equilibrado"
                : data.valor_restante > 0
                ? `Faltam ${data.valor_restante.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}`
                : `Sobra ${Math.abs(data.valor_restante).toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}`;
    }

    // Escuta mudanças e recalcula
    form.querySelectorAll("input").forEach((input) => {
        input.addEventListener("input", calcularBackend);
    });

    calcularBackend();
});
