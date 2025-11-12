@php
// O Orchid espera que os assets sejam registrados na seção de scripts
@endphp

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ... Funções cleanAndParse e formatToBRL ...

        /**
         * Converte um valor formatado (ex: "R$ 1.253,02") em um float (ex: 1253.02).
         * @param {string} value
         * @returns {number}
         */
        function cleanAndParse(value) {
            if (!value) return 0;
            // Remove prefixo (R$), separadores de milhar (.), e troca vírgula (,) por ponto (.)
            return parseFloat(value.replace('R$', '').replace(/\./g, '').replace(',', '.').trim());
        }

        /**
         * Formata um número (float) no padrão de moeda brasileira (ex: "R$ 1.253,02").
         * @param {number} number
         * @returns {string}
         */
        function formatToBRL(number) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(number);
        }

        // 2. Elementos: AGORA USANDO SELETORES DE ID
        const valor1Element = document.getElementById('valor1');
        const valor2Element = document.getElementById('valor2');
        const resultElement = document.getElementById('resultado');

        const inputElements = [valor1Element, valor2Element];

        if (!valor1Element || !valor2Element || !resultElement) {
            // Se os elementos não forem encontrados, para a execução.
            console.error('Um ou mais elementos de input/resultado não foram encontrados. Verifique os IDs.');
            return; 
        }

        // 3. Lógica de Cálculo
        function calculateAndDisplay() {
            // Pega o valor, limpa a máscara e soma
            let total = cleanAndParse(valor1Element.value) + cleanAndParse(valor2Element.value);

            // Formata e exibe o resultado
            resultElement.value = formatToBRL(total);
        }

        // 4. Atachando Eventos
        inputElements.forEach(input => {
            // Usa 'input' para capturar cada tecla digitada
            input.addEventListener('input', calculateAndDisplay);
            // Também dispara o cálculo ao carregar a página (caso os valores iniciais não sejam zero)
        });

        // Dispara o cálculo inicial
        calculateAndDisplay(); 

    });
</script>
@endpush