import Inputmask from 'inputmask';

document.addEventListener('DOMContentLoaded', () => {
    const inputs = {
        valor1: document.querySelector('input[name="valor1"]'),
        valor2: document.querySelector('input[name="valor2"]'),
        resultado: document.querySelector('input[name="resultado"]'),
    };

    if (!inputs.valor1 || !inputs.valor2 || !inputs.resultado) {
        console.error('[Calculadora] Um ou mais elementos de entrada não foram encontrados.');
        return;
    }

    Object.keys(inputs).forEach(key => {
        const input = inputs[key];
        const maskConfig = JSON.parse(input.getAttribute('data-mask') || '{}');
        if (maskConfig) {
            Inputmask(maskConfig).mask(input);
        }
    });

    const updateResult = () => {
        try {
            const v1 = parseFloat(inputs.valor1.value.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
            const v2 = parseFloat(inputs.valor2.value.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
            const result = v1 + v2;
            inputs.resultado.value = isNaN(result) ? '0,00' : result.toFixed(2).replace('.', ',');
        } catch (error) {
            console.error('[Calculadora] Erro ao calcular o resultado:', error);
            inputs.resultado.value = '0,00';
        }
    };

    Object.values(inputs).forEach(input => {
        if (input && input !== inputs.resultado) {
            input.addEventListener('input', updateResult);
        }
    });

    updateResult();
});
