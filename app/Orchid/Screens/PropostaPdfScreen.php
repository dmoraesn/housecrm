<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Proposta;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PropostaPdfScreen extends Screen
{
    public Proposta $proposta;

    public function query(Proposta $proposta): array
    {
        $this->proposta = $proposta->load(['lead.corretor', 'imovel']);

        return [
            'proposta' => $proposta,
        ];
    }

    public function name(): ?string
    {
        return "Proposta #{$this->proposta->id}";
    }

    public function description(): ?string
    {
        return 'Visualização para impressão ou download em PDF.';
    }

    public function commandBar(): array
    {
        return [
            \Orchid\Screen\Actions\Button::make('Imprimir')
                ->icon('bs.printer')
                ->method('print'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::view('platform.propostas.pdf'),
        ];
    }

    public function print()
    {
        return view('platform.propostas.pdf', ['proposta' => $this->proposta]);
    }
}