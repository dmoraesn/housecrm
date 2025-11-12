import Inputmask from 'inputmask';

// Função que inicia o cálculo na carga inicial e em recargas de tela (Orchid/Turbo)
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('screen:load', initializeFluxoCalculator);
    initializeFluxoCalculator();
});

function initializeFluxoCalculator() {
    const inputs = {
        valor1: document.querySelector('input[name="valor1"]'),
        valor2: document.querySelector('input[name="valor2"]'),
        resultado: document.querySelector('input[name="resultado"]'),
    };

    if (!inputs.valor1 || !inputs.valor2 || !inputs.resultado) {
        return; 
    }

    // 1. Aplica as máscaras, limpando instâncias antigas para estabilidade
    Object.keys(inputs).forEach(key => {
        const input = inputs[key];
        
        // Remove a instância Inputmask anterior antes de criar a nova
        if (input.inputmask) { 
            input.inputmask.remove(); 
        }

        const maskConfig = JSON.parse(input.getAttribute('data-mask') || '{}');
        if (Object.keys(maskConfig).length > 0) {
            Inputmask(maskConfig).mask(input);
        }
    });

    // 2. Função de cálculo e atualização em tempo real
    const updateResult = () => {
        try {
            // Se a máscara falhou em um dos campos (o que causava o erro anterior), saímos.
            if (!inputs.valor1.inputmask || !inputs.valor2.inputmask) {
                return;
            }
            
            // Obtém a string numérica pura (string de centavos, ex: "10022212")
            const rawV1 = inputs.valor1.inputmask.unmaskedvalue();
            const rawV2 = inputs.valor2.inputmask.unmaskedvalue();
            
            // Converte para valor float real (dividido por 100)
            const v1 = parseFloat(rawV1) / 100;
            const v2 = parseFloat(rawV2) / 100;
            
            const result = v1 + v2;
            
            if (isNaN(result)) {
                inputs.resultado.value = '0,00';
                return;
            }

            // Formata o resultado para a string de texto com vírgula decimal (ex: "100.222,12")
            const formattedResult = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(result);
            
            // Atualiza o campo. O Inputmask do campo 'resultado' aplicará a máscara R$.
            inputs.resultado.value = formattedResult;

        } catch (error) {
            console.error('[Calculadora] Erro durante o cálculo instantâneo:', error);
            inputs.resultado.value = '0,00'; 
        }
    };

    // 3. Adiciona listeners de 'input' para instantaneidade
    const inputsToListen = [inputs.valor1, inputs.valor2];
    
    inputsToListen.forEach(input => {
        // Remove listeners antigos e adiciona o novo
        input.removeEventListener('input', updateResult); 
        input.addEventListener('input', updateResult);
    });

    // Executa na inicialização
    updateResult();
}