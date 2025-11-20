<script>
// =======================================================================
// TRIGGER AUTOMÁTICO — Reativa o JS sempre que o Orchid recarregar a tela
// =======================================================================
function ativarFluxoJS() {
    console.log("FluxoScreen: JS de cálculo reativado automaticamente.");

    if (typeof iniciarCalculoFluxo === "function") {
        iniciarCalculoFluxo();
    }
}

document.addEventListener("DOMContentLoaded", ativarFluxoJS);
document.addEventListener("orchid:screen:load", ativarFluxoJS);
document.addEventListener("orchid:screen:render", ativarFluxoJS);
document.addEventListener("turbo:load", ativarFluxoJS);
</script>



<script>
// =======================================================================
// TODO O SEU JS ORIGINAL — APENAS ENCAPSULADO
// =======================================================================

function iniciarCalculoFluxo() {

    console.log("FluxoScreen: iniciarCalculoFluxo() executado.");

    // =========================================================
    // Funções de formatação BRL
    // =========================================================
    const parse = v =>
        v ? parseFloat(String(v).replace(/[R$\s]/g, '').replace(/\./g, '').replace(',', '.')) || 0 : 0;

    const format = v =>
        v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    const get = id =>
        document.getElementById(id)?.value || '0';

    const set = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = format(val);
    };

    const debounce = (fn, delay = 200) => {
        clearTimeout(window._t);
        window._t = setTimeout(fn, delay);
    };

    // =========================================================
    // Função principal de cálculo
    // =========================================================
    const recalc = () => {

        const imovel       = parse(get('valor_imovel'));
        const avaliacao    = parse(get('valor_avaliacao'));
        const bonus        = parse(get('valor_bonus_descontos'));
        const assinatura   = parse(get('valor_assinatura_contrato'));
        const chaves       = parse(get('valor_na_chaves'));
        const parcelas     = parseInt(get('parcelas_qtd') || '0');
        let financiado     = parse(get('valor_financiado'));
        let perc           = parseFloat(get('financiamento_percentual') || '80');

        const modo = document.querySelector('input[name="fluxo[modo_calculo]"]:checked')?.value || 'percentual';
        const base = document.querySelector('input[name="fluxo[base_calculo]"]:checked')?.value || 'avaliacao';

        const baseValor = base === 'avaliacao' ? avaliacao : imovel;

        // -----------------------------------------------------
        // Percentual x Manual
        // -----------------------------------------------------
        if (modo === 'percentual' && baseValor > 0) {

            financiado = baseValor * (perc / 100);
            set('valor_financiado', financiado);

            document.getElementById('valor_financiado').readOnly = true;
            document.getElementById('financiamento_percentual').readOnly = false;

        } else if (modo === 'manual' && baseValor > 0) {

            perc = (financiado / baseValor) * 100;

            document.getElementById('financiamento_percentual').value = Math.round(perc);
            document.getElementById('valor_financiado').readOnly = false;
            document.getElementById('financiamento_percentual').readOnly = true;
        }

        if (document.getElementById('percentualLabel')) {
            percentualLabel.value = `${Math.round(perc)}%`;
        }

        // -----------------------------------------------------
        // Cálculos principais
        // -----------------------------------------------------
        const diferenca = avaliacao - imovel;
        const corr = financiado + diferenca;

        const entrada = imovel - financiado - bonus;
        set('entrada_minima', entrada);

        // Soma dos balões
        let totalBaloes = 0;

        document.querySelectorAll('[name^="fluxo[baloes]["][name$="[valor]"]').forEach(e => {
            totalBaloes += parse(e.value || '0');
        });

        const pagos = assinatura + chaves + totalBaloes;

        let vparc = 0;
        let tparc = 0;
        if (parcelas > 0) {
            const resto = Math.max(entrada - pagos, 0);
            vparc = resto / parcelas;
            tparc = vparc * parcelas;
        }

        const totalEntrada = pagos + tparc;
        const saldo = entrada - totalEntrada;

        set('valor_parcela', vparc);
        set('total_parcelamento', tparc);
        set('valor_total_entrada', totalEntrada);
        set('valor_restante', saldo);

        // -----------------------------------------------------
        // Painel flutuante
        // -----------------------------------------------------
        const saldoTopo     = document.getElementById('saldoRestanteTopo');
        const difAvaliacao  = document.getElementById('difAvaliacao');
        const finCorrigido  = document.getElementById('finCorrigido');

        if (saldoTopo)     saldoTopo.textContent = format(saldo);
        if (difAvaliacao)  difAvaliacao.textContent = (diferenca >= 0 ? '+' : '') + format(diferenca);
        if (finCorrigido)  finCorrigido.textContent = format(corr);
    };



    // =========================================================
    // Listeners automáticos
    // =========================================================
    document.querySelectorAll('input').forEach(el => {
        el.addEventListener('keyup', () => debounce(recalc));
        el.addEventListener('change', () => debounce(recalc));
        el.addEventListener('input', () => debounce(recalc));
    });

    document.querySelectorAll('input[name="fluxo[modo_calculo]"], input[name="fluxo[base_calculo]"]').forEach(el => {
        el.addEventListener('change', () => recalc());
    });

    const observer = new MutationObserver(() => debounce(recalc));
    observer.observe(document.body, { childList: true, subtree: true });

    recalc();
}
</script>
