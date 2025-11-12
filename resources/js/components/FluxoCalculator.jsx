import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';

function FluxoCalculator() {
    const [valor, setValor] = useState('');

    const handleChange = (e) => {
        let val = e.target.value.replace(/\D/g, ''); // remove não numéricos
        val = (val / 100).toFixed(2) + '';
        val = val.replace('.', ',');
        val = val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        setValor('R$ ' + val);
    };

    return (
        <div className="bg-white rounded-lg shadow-md p-4 mt-4">
            <h4 className="text-lg font-semibold mb-2">Pré-visualização Dinâmica</h4>
            <input
                type="text"
                value={valor}
                onChange={handleChange}
                placeholder="Digite o valor..."
                className="form-control"
            />
            <p className="mt-2 text-gray-600">
                Valor formatado automaticamente: <strong>{valor || 'R$ 0,00'}</strong>
            </p>
        </div>
    );
}

// Renderiza automaticamente no placeholder da tela
if (document.getElementById('fluxo-calculator')) {
    const root = ReactDOM.createRoot(document.getElementById('fluxo-calculator'));
    root.render(<FluxoCalculator />);
}
