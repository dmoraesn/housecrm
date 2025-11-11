{{-- resources/views/platform/propostas/simulador.blade.php --}}

<div class="row">
    <!-- Seção de Inputs -->
    <div class="col-md-7">
        <div class="card p-4">
            <h2 class="h5 fw-semibold mb-4 d-flex align-items-center">
                <i class="bi bi-calculator me-2"></i> Simulador de Plano de Entrada
            </h2>
            <form id="propostaForm" class="row g-3" method="POST" action="">
                @csrf
                @if($proposta->exists) @method('PUT') @endif
                <!-- Campos Ocultos -->
                <input type="hidden" id="valorEntradaHidden" name="proposta[valor_entrada]" value="{{ $proposta->valor_entrada ?? 0 }}">
                <input type="hidden" id="totalParcelamentoHidden" name="proposta[total_parcelamento]" value="{{ $proposta->total_parcelamento ?? 0 }}">
                <input type="hidden" id="valorRestanteHidden" name="proposta[valor_restante]" value="{{ $proposta->valor_restante ?? 0 }}">
                <input type="hidden" id="baloesJsonHidden" name="proposta[baloes_json]" value="{{ $proposta->baloes_json ? json_encode($proposta->baloes_json) : '[]' }}">
                <!-- Valor e Descontos -->
                <div class="col-12">
                    <h3 class="h6 fw-medium border-bottom pb-2 mb-3 d-flex align-items-center">
                        <i class="bi bi-cash-coin me-2 text-success"></i> Valor e Descontos
                    </h3>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valor Real do Bem (R$)</label>
                    <input type="text" inputmode="decimal" class="form-control currency-input" id="valorRealInput" name="proposta[valor_real]" value="{{ $proposta->valor_real ?? 0 }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valor a Financiar (R$)</label>
                    <input type="text" inputmode="decimal" class="form-control currency-input" id="valorFinanciadoInput" name="proposta[valor_financiado]" value="{{ $proposta->valor_financiado ?? 0 }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Descontos Concedidos (R$)</label>
                    <input type="text" inputmode="decimal" class="form-control currency-input" id="descontosInput" name="proposta[descontos]" value="{{ $proposta->descontos ?? 0 }}">
                </div>
                <!-- Entrada e Balões -->
                <div class="col-12 mt-4">
                    <h3 class="h6 fw-medium border-bottom pb-2 mb-3 d-flex align-items-center">
                        <i class="bi bi-calendar-plus me-2 text-primary"></i> Entrada e Balões
                    </h3>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valor da Assinatura (R$)</label>
                    <input type="text" inputmode="decimal" class="form-control currency-input" id="valorAssinaturaInput" name="proposta[valor_assinatura]" value="{{ $proposta->valor_assinatura ?? 0 }}">
                </div>
                <div class="col-12">
                    <div id="baloesContainer" class="row g-2"></div>
                    <button type="button" id="addBalaoBtn" class="btn btn-sm btn-outline-primary mt-2 d-flex align-items-center">
                        <i class="bi bi-plus-circle me-1"></i> Adicionar Balão
                    </button>
                </div>
                <!-- Parcelamento -->
                <div class="col-12 mt-4">
                    <h3 class="h6 fw-medium border-bottom pb-2 mb-3 d-flex align-items-center">
                        <i class="bi bi-calendar-date me-2 text-info"></i> Parcelamento
                    </h3>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valor da Parcela Mensal (R$)</label>
                    <input type="text" inputmode="decimal" class="form-control currency-input" id="valorParcelaInput" name="proposta[valor_parcela]" value="{{ $proposta->valor_parcela ?? 0 }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Número de Parcelas (unid.)</label>
                    <input type="number" class="form-control" id="numParcelasInput" name="proposta[num_parcelas]" value="{{ $proposta->num_parcelas ?? 0 }}" min="0" step="1">
                </div>
            </form>
        </div>
    </div>
    <!-- Resumo Financeiro -->
    <div class="col-md-5">
        <div class="card p-4 bg-light border-start border-primary border-3 shadow-sm">
            <h2 class="h5 fw-bold mb-4 text-primary d-flex align-items-center">
                <i class="bi bi-receipt me-2"></i> Resumo Financeiro
            </h2>
            <div class="space-y-3">
                <div class="border-bottom pb-3">
                    <div class="d-flex justify-content-between text-sm text-muted">
                        <span>Valor Real do Bem:</span>
                        <span id="valorRealOriginalSpan">R$ 0,00</span>
                    </div>
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-muted">(-) Descontos:</span>
                        <span id="descontosSpan" class="text-danger">R$ 0,00</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Valor Líquido:</span>
                        <span id="valorImovelSpan">R$ 0,00</span>
                    </div>
                </div>
                <div class="p-3 rounded border border-info bg-light">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold">Entrada Total:</span>
                        <span id="valorEntradaSpan" class="h4 text-success">R$ 0,00</span>
                    </div>
                    <p class="text-xs text-muted mb-2">
                        Assinatura: <span id="porcentagemPaga">0%</span> da entrada.
                    </p>
                    <div class="progress" style="height: 10px;">
                        <div id="barraProgresso" class="progress-bar bg-success" style="width: 0%"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span id="numParcelasSpan">0</span> x <span id="valorParcelaSpan">R$ 0,00</span>
                        <small class="d-block text-muted">Total do Parcelamento</small>
                    </div>
                    <span id="totalParcelamentoSpan" class="h6 text-dark">R$ 0,00</span>
                </div>
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 fw-bold">DIFERENÇA TOTAL:</span>
                        <span id="valorRestanteSpan" class="h3">R$ 0,00</span>
                    </div>
                    <p class="text-xs mt-1" id="restanteDesc"></p>
                </div>
                <div id="validationMessage" class="text-center mt-3 p-2 rounded small">
                    Aguardando dados...
                </div>
            </div>
        </div>
    </div>
</div>
<template id="balaoTemplate">
    <div class="balao-row-wrapper row g-2 mb-2 align-items-end">
        <div class="col-6">
            <input type="text" inputmode="decimal" class="form-control currency-input balao-valor" placeholder="R$ 0,00">
        </div>
        <div class="col-5">
            <input type="date" class="form-control balao-data">
        </div>
        <div class="col-1">
            <button type="button" class="btn btn-sm btn-outline-danger remove-balao-btn">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>
<script>
    /**
     * Simulador de Proposta - Compatível com Turbo (Orchid)
     * Evento: DOMContentLoaded
     */
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('propostaForm');
        if (!form) return;
        const DEBOUNCE_DELAY = 300;
        const HOJE = new Date().toISOString().split('T')[0];
        let calcTimeout;
        const els = {
            inputs: {
                valorReal: document.getElementById('valorRealInput'),
                valorFinanciado: document.getElementById('valorFinanciadoInput'),
                descontos: document.getElementById('descontosInput'),
                valorAssinatura: document.getElementById('valorAssinaturaInput'),
                valorParcela: document.getElementById('valorParcelaInput'),
                numParcelas: document.getElementById('numParcelasInput'),
            },
            hidden: {
                entrada: document.getElementById('valorEntradaHidden'),
                parcelamento: document.getElementById('totalParcelamentoHidden'),
                restante: document.getElementById('valorRestanteHidden'),
                baloes: document.getElementById('baloesJsonHidden'),
            },
            display: {
                valorReal: document.getElementById('valorRealOriginalSpan'),
                descontos: document.getElementById('descontosSpan'),
                valorImovel: document.getElementById('valorImovelSpan'),
                entrada: document.getElementById('valorEntradaSpan'),
                porcentagem: document.getElementById('porcentagemPaga'),
                progresso: document.getElementById('barraProgresso'),
                numParcelas: document.getElementById('numParcelasSpan'),
                valorParcela: document.getElementById('valorParcelaSpan'),
                totalParcelamento: document.getElementById('totalParcelamentoSpan'),
                restante: document.getElementById('valorRestanteSpan'),
                desc: document.getElementById('restanteDesc'),
                validacao: document.getElementById('validationMessage'),
            },
            baloesContainer: document.getElementById('baloesContainer'),
            addBalaoBtn: document.getElementById('addBalaoBtn'),
            template: document.getElementById('balaoTemplate'),
        };
        const toNumber = (str) => parseFloat(String(str).replace(/\D/g, '')) / 100 || 0;
        const toBRL = (num) => num.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        const toPercent = (part, total) => total > 0 ? ((part / total) * 100).toFixed(1) + '%' : '0%';
        const formatCurrency = (num) => num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const formatInput = (input) => {
            let value = input.value.replace(/\D/g, '');
            if (!value) { input.value = ''; return; }
            value = (value / 100).toFixed(2);
            const [int, dec] = value.split('.');
            input.value = int.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + dec;
        };
        const criarBalao = (valor = 0, data = HOJE) => {
            const clone = els.template.content.cloneNode(true);
            const wrapper = clone.querySelector('.balao-row-wrapper');
            const inputValor = wrapper.querySelector('.balao-valor');
            const inputData = wrapper.querySelector('.balao-data');
            const btnRemover = wrapper.querySelector('.remove-balao-btn');
            inputValor.value = formatCurrency(valor);
            inputData.value = data;
            inputData.min = HOJE;
            inputValor.addEventListener('input', () => formatInput(inputValor));
            inputValor.addEventListener('blur', () => { if (!inputValor.value) inputValor.value = '0,00'; debouncedCalculate(); });
            inputData.addEventListener('change', debouncedCalculate);
            btnRemover.addEventListener('click', () => { wrapper.remove(); debouncedCalculate(); });
            els.baloesContainer.appendChild(wrapper);
        };
        const coletarBaloes = () => {
            const baloes = [];
            document.querySelectorAll('.balao-row-wrapper').forEach(row => {
                const valor = toNumber(row.querySelector('.balao-valor').value);
                const data = row.querySelector('.balao-data').value;
                if (valor > 0 && data) baloes.push({ valor, data });
            });
            return baloes;
        };
        const calculate = () => {
            const vr = toNumber(els.inputs.valorReal.value);
            const vf = toNumber(els.inputs.valorFinanciado.value);
            const d = toNumber(els.inputs.descontos.value);
            const va = toNumber(els.inputs.valorAssinatura.value);
            const vp = toNumber(els.inputs.valorParcela.value);
            const np = parseInt(els.inputs.numParcelas.value) || 0;
            const baloes = coletarBaloes();
            const totalBaloes = baloes.reduce((s, b) => s + b.valor, 0);
            const entradaTotal = va + totalBaloes;
            const parcelamentoTotal = vp * np;
            const valorLiquido = vr - d;
            const diferenca = valorLiquido - entradaTotal - parcelamentoTotal - vf;
            els.hidden.entrada.value = entradaTotal;
            els.hidden.parcelamento.value = parcelamentoTotal;
            els.hidden.restante.value = diferenca;
            els.hidden.baloes.value = JSON.stringify(baloes);
            els.display.valorReal.textContent = toBRL(vr);
            els.display.descontos.textContent = toBRL(d);
            els.display.valorImovel.textContent = toBRL(valorLiquido);
            els.display.entrada.textContent = toBRL(entradaTotal);
            els.display.porcentagem.textContent = toPercent(va, entradaTotal);
            els.display.progresso.style.width = toPercent(entradaTotal, valorLiquido);
            els.display.numParcelas.textContent = np;
            els.display.valorParcela.textContent = toBRL(vp);
            els.display.totalParcelamento.textContent = toBRL(parcelamentoTotal);
            els.display.restante.textContent = toBRL(Math.abs(diferenca));
            const validacao = els.display.validacao;
            const restanteSpan = els.display.restante;
            const desc = els.display.desc;
            [validacao, restanteSpan, desc].forEach(el => el.className = el.className.replace(/bg-\w+|text-\w+|border-\w+/g, '').trim());
            if (Math.abs(diferenca) < 0.01) {
                validacao.textContent = 'Plano 100% equilibrado.';
                validacao.classList.add('bg-success-subtle', 'text-success');
                restanteSpan.classList.add('text-success');
                desc.textContent = 'Entrada + Parcelamento + Financiamento = Valor Líquido.';
                desc.classList.add('text-success');
            } else if (diferenca > 0.01) {
                validacao.textContent = `FALTAM ${toBRL(diferenca)}`;
                validacao.classList.add('bg-danger-subtle', 'text-danger');
                restanteSpan.classList.add('text-danger');
                desc.textContent = 'Aumente entrada, parcelas ou financiamento.';
                desc.classList.add('text-danger');
            } else {
                validacao.textContent = `SOBRA ${toBRL(Math.abs(diferenca))}`;
                validacao.classList.add('bg-warning-subtle', 'text-warning');
                restanteSpan.classList.add('text-warning');
                desc.textContent = 'Cliente pagará a mais. Reduza valores.';
                desc.classList.add('text-warning');
            }
        };
        const debouncedCalculate = () => {
            clearTimeout(calcTimeout);
            calcTimeout = setTimeout(calculate, DEBOUNCE_DELAY);
        };
        Object.values(els.inputs).forEach(input => {
            if (!input) return;
            if (input.classList.contains('currency-input')) {
                input.addEventListener('input', () => { formatInput(input); debouncedCalculate(); });
                input.addEventListener('blur', () => { if (!input.value) input.value = '0,00'; debouncedCalculate(); });
            } else {
                input.addEventListener('input', debouncedCalculate);
            }
        });
        els.addBalaoBtn.addEventListener('click', () => criarBalao());
        // Inicialização
        let baloesSalvos = [];
        try { baloesSalvos = JSON.parse(els.hidden.baloes.value || '[]'); } catch (e) {}
        baloesSalvos.forEach(b => criarBalao(b.valor, b.data));
        document.querySelectorAll('.currency-input').forEach(input => {
            const num = toNumber(input.value);
            input.value = num > 0 ? formatCurrency(num) : '';
        });
        calculate();
    });
</script>