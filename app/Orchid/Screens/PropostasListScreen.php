<?php
declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Proposta;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;

class PropostasListScreen extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Propostas';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Listagem e gerenciamento de propostas.';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        return [
            'propostas' => Proposta::filters()->defaultSort('id', 'desc')->paginate(),
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Link::make('Nova Proposta')
                ->icon('bs.plus')
                ->route('platform.propostas.create'),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::table('propostas', [
                TD::make('id', '#')->width(80),
                TD::make('data_assinatura', 'Data')
                    ->render(fn (Proposta $p) => $p->data_assinatura?->format('d/m/Y')),
                TD::make('valor_real', 'Valor do Imóvel')
                    ->render(fn (Proposta $p) => number_format($p->valor_real, 2, ',', '.')),
                TD::make('status', 'Status'),
            ]),
        ];
    }
}
// 43 linhas mantidas