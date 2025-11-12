<script>
document.addEventListener("DOMContentLoaded", () => {
    const parse = (v) => v ? parseFloat(String(v).replace(/[R$\s]/g, '').replace(/\./g, '').replace(',', '.')) || 0 : 0;
    const format = (v) => v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const get = (id) => document.getElementById(id)?.value || '0';
    const set = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = format(val);
    };

    let timer;
    const debounce = (fn, delay = 200) => { clearTimeout(timer); timer = setTimeout(fn, delay); };

    const recalc = () => {
        const valorImovel = parse(get('valor_imovel'));
        const valorAvaliacao = parse(get('valor_avaliacao'));
        const bonus = parse(get('valor_bonus_descontos'));
        const assinatura = parse(get('valor_assinatura_contrato'));
        const chaves = parse(get('valor_na_chaves'));
        const parcelasQtd = parseInt(get('parcelas_qtd') || '0');
        const valorFinanciado = parse(get('valor_financiado'));

        const financiamentoSugerido = valorAvaliacao * 0.8;
        set('valor_financiamento_sugerido', financiamentoSugerido);

        let totalBaloes = 0;
        document.querySelectorAll('[name^="fluxo[baloes]["][name$="[valor]"]').forEach(el => {
            totalBaloes += parse(el.value || '0');
        });

        const diferencaAvaliacao = valorAvaliacao - valorImovel;
        const financiamentoCorrigido = valorFinanciado + diferencaAvaliacao;
        const entradaMinima = valorImovel - valorFinanciado - bonus;
        set('entrada_minima', entradaMinima);

        const pagosFixos = assinatura + chaves + totalBaloes;
        let valorParcela = 0, totalParcelamento = 0;
        if (parcelasQtd > 0) {
            const restanteAParcelar = Math.max(entradaMinima - pagosFixos, 0);
            valorParcela = restanteAParcelar / parcelasQtd;
            totalParcelamento = valorParcela * parcelasQtd;
        }

        const entradaTotal = pagosFixos + totalParcelamento;
        const saldoRestante = entradaMinima - entradaTotal;

        set('valor_parcela', valorParcela);
        set('total_parcelamento', totalParcelamento);
        set('valor_total_entrada', entradaTotal);
        set('valor_restante', saldoRestante);

        const saldo = document.getElementById('saldoRestanteTopo');
        if (saldo) {
            saldo.textContent = format(saldoRestante);
            saldo.style.color = saldoRestante > 0 ? '#dc2626' : saldoRestante < 0 ? '#16a34a' : '#1d4ed8';
        }

        const difAvaliacaoEl = document.getElementById('difAvaliacao');
        if (difAvaliacaoEl) difAvaliacaoEl.textContent = (diferencaAvaliacao >= 0 ? '+' : '') + format(diferencaAvaliacao);

        const finCorrigidoEl = document.getElementById('finCorrigido');
        if (finCorrigidoEl) finCorrigidoEl.textContent = format(financiamentoCorrigido);
    };

    const notification = document.getElementById('calcNotification');
    const toggleButton = document.getElementById('toggleCalcButton');
    const iconMinimize = document.getElementById('iconMinimize');
    const details = document.getElementById('calcDetails');
    const saldoTopo = document.getElementById('saldoRestanteTopo');

    let isMinimized = false;
    const toggleMinimize = () => {
        isMinimized = !isMinimized;
        notification.classList.toggle('minimized', isMinimized);
        details.style.display = isMinimized ? 'none' : 'block';
        iconMinimize.setAttribute('d', isMinimized
            ? 'M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z'
            : 'M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z');
        toggleButton.title = isMinimized ? "Maximizar" : "Minimizar";
    };

    toggleButton.addEventListener('click', toggleMinimize);
    saldoTopo.addEventListener('click', () => { if (isMinimized) toggleMinimize(); });

    document.querySelectorAll('input').forEach(el => {
        el.addEventListener('keyup', () => debounce(recalc));
        el.addEventListener('change', () => debounce(recalc));
    });

    const container = document.querySelector('[name="fluxo[baloes]"]')?.closest('div') || document.body;
    const observer = new MutationObserver(() => debounce(recalc));
    observer.observe(container, { childList: true, subtree: true });

    const valorFinanciadoEl = document.getElementById('valor_financiado');
    const valorAvaliacaoInicial = parse(get('valor_avaliacao'));
    if (parse(valorFinanciadoEl?.value) === 0 && valorAvaliacaoInicial > 0) {
        const financiamentoSugeridoInicial = valorAvaliacaoInicial * 0.8;
        set('valor_financiado', financiamentoSugeridoInicial);
    }

    recalc();
});
</script>
