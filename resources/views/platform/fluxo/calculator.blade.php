{{-- resources/views/platform/fluxo/calculator.blade.php --}}

@php
    $data = $query ?? ['valor1' => 0, 'valor2' => 0, 'resultado' => 0];

    $maskConfig = [
        'alias' => 'numeric',
        'groupSeparator' => '.',
        'radixPoint' => ',',
        'digits' => 2,
        'autoGroup' => true,
        'prefix' => 'R$ ',
        'digitsOptional' => false,
        'placeholder' => '0',
        'rightAlign' => false,
        'removeMaskOnSubmit' => true,
    ];
@endphp

<div class="p-4 bg-white rounded shadow-sm">
    <h5 class="mb-3 fw-bold text-primary">💰 Calculadora de Fluxo</h5>

    <form class="row g-3">
        <div class="col-md-4">
            <label for="valor1" class="form-label">Valor 1</label>
            <input type="text" id="valor1" name="valor1" class="form-control"
                value="{{ number_format($data['valor1'], 2, ',', '.') }}"
                data-mask='@json($maskConfig)' autocomplete="off" />
        </div>

        <div class="col-md-4">
            <label for="valor2" class="form-label">Valor 2</label>
            <input type="text" id="valor2" name="valor2" class="form-control"
                value="{{ number_format($data['valor2'], 2, ',', '.') }}"
                data-mask='@json($maskConfig)' autocomplete="off" />
        </div>

        <div class="col-md-4">
            <label for="resultado" class="form-label">Resultado</label>
            <input type="text" id="resultado" name="resultado" class="form-control bg-light"
                value="{{ number_format($data['resultado'], 2, ',', '.') }}"
                readonly />
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', initFluxoCalc);
document.addEventListener('screen:load', initFluxoCalc);

function initFluxoCalc() {
    const $inputs = {
        valor1: $('#valor1'),
        valor2: $('#valor2'),
        resultado: $('#resultado'),
    };

    // Aplica máscaras
    Object.values($inputs).forEach(($el) => {
        const config = $el.data('mask');
        $el.inputmask('remove'); // remove máscara anterior
        $el.inputmask(config);
    });

    const calcular = () => {
        const v1 = parseFloat($inputs.valor1.inputmask('unmaskedvalue') || 0) / 100;
        const v2 = parseFloat($inputs.valor2.inputmask('unmaskedvalue') || 0) / 100;
        const soma = v1 + v2;

        const formatted = soma.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        $inputs.resultado.val(formatted);
    };

    $inputs.valor1.on('input', calcular);
    $inputs.valor2.on('input', calcular);

    calcular(); // inicializa
}
</script>
@endpush
