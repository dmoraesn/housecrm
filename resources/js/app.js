import * as Turbo from '@hotwired/turbo';
import * as Bootstrap from 'bootstrap';

import { Application } from '@hotwired/stimulus';
import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';
import ApplicationController from './controllers/application_controller';
// import Orchid from "./orchid"; <--- ESTA LINHA FOI REMOVIDA

import Inputmask from 'inputmask';

window.Turbo = Turbo;
window.Bootstrap = Bootstrap;
window.application = Application.start();
window.Controller = ApplicationController;
// window.Orchid = Orchid; <--- ESTA LINHA FOI REMOVIDA

const context = require.context('./controllers', true, /\.js$/);
application.load(definitionsFromContext(context));

window.addEventListener('turbo:before-fetch-request', (event) => {
    let state = document.getElementById('screen-state')?.value;

    if (state && state.length > 0) {
        event.detail?.fetchOptions?.body?.append('_state', state);
    }
});

// A partir daqui é o seu código de calculadora (que não é o padrão do Laravel/Orchid, mas deve ser mantido)

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