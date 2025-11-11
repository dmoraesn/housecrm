<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;

class FluxoScreen extends Screen
{
    public $name = 'Teste de Cálculo';
    public $description = 'Teste com duas caixas de texto e resultado automático';
    public $permission = 'platform.fluxo';

    /**
     * Inicializa os dados da tela.
     */
    public function query(): array
    {
        return [
            'valor1' => 0.00,
            'valor2' => 0.00,
            'resultado' => 0.00,
        ];
    }

    /**
     * Define a barra de comandos (vazia neste caso).
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * Configura o layout da tela com campos de entrada.
     */
    public function layout(): array
    {
        $data = $this->query();

        return [
            Layout::rows([
                Input::make('valor1')
                    ->title('Valor 1')
                    ->type('number')
                    ->step('0.01')
                    ->value(number_format($data['valor1'], 2, ',', '.'))
                    ->attribute('data-mask', json_encode($this->getCurrencyMask())),

                Input::make('valor2')
                    ->title('Valor 2')
                    ->type('number')
                    ->step('0.01')
                    ->value(number_format($data['valor2'], 2, ',', '.'))
                    ->attribute('data-mask', json_encode($this->getCurrencyMask())),

                Input::make('resultado')
                    ->title('Resultado')
                    ->type('number')
                    ->step('0.01')
                    ->value(number_format($data['resultado'], 2, ',', '.'))
                    ->attribute('data-mask', json_encode($this->getCurrencyMask()))
                    ->readonly(),
            ]),
        ];
    }

    /**
     * Retorna a configuração da máscara de moeda.
     */
    private function getCurrencyMask(): array
    {
        return [
            'alias' => 'numeric',
            'groupSeparator' => '.',
            'radixPoint' => ',',
            'digits' => 2,
            'autoGroup' => true,
            'prefix' => 'R$ ',
        ];
    }

    /**
     * Método reservado para ações (não utilizado neste caso).
     */
    public function method(): void
    {
        // Vazio, pois o cálculo é feito via JavaScript
    }
}
