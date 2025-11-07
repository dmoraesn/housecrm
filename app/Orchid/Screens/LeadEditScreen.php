<?php

namespace App\Orchid\Screens;

use App\Models\Lead;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Alert;
use Orchid\Screen\Actions\Button;

class LeadEditScreen extends Screen
{
    /**
     * @var Lead|null
     */
    public ?Lead $lead = null;

    /**
     * Query data.
     */
    public function query(Lead $lead): array
    {
        return [
            'lead' => $lead,
        ];
    }

    /**
     * Name and description.
     */
    public function name(): ?string
    {
        return $this->lead->exists
            ? 'Editar Lead'
            : 'Novo Lead';
    }

    public function description(): ?string
    {
        return 'Gerencie informações e status do lead.';
    }

    /**
     * Actions bar.
     */
    public function commandBar(): array
    {
        return [
            Button::make('Salvar')
                ->icon('check')
                ->method('save'),

            Button::make('Excluir')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->lead->exists),
        ];
    }

    /**
     * Screen layout.
     */
    public function layout(): array
    {
        return [
            Layout::rows([
                Input::make('lead.nome')
                    ->title('Nome do Lead')
                    ->placeholder('Ex: João Silva')
                    ->required(),

                Input::make('lead.telefone')
                    ->title('Telefone')
                    ->mask('(99) 99999-9999')
                    ->placeholder('(99) 99999-9999'),

                Input::make('lead.email')
                    ->title('E-mail')
                    ->type('email')
                    ->placeholder('exemplo@email.com'),

                Input::make('lead.origem')
                    ->title('Origem')
                    ->placeholder('Ex: Google, Indicação, Site, Instagram'),

                Select::make('lead.status')
                    ->title('Etapa do Funil')
                    ->options([
                        'novo' => '1️⃣ Novo Lead / Descoberta',
                        'qualificacao' => '2️⃣ Qualificação / Entendimento',
                        'visita' => '3️⃣ Apresentação / Visita',
                        'negociacao' => '4️⃣ Proposta / Negociação',
                        'fechamento' => '5️⃣ Fechamento / Contrato',
                    ])
                    ->required(),

                Input::make('lead.corretor')
                    ->title('Corretor Responsável')
                    ->placeholder('Ex: Ana Lima'),

                TextArea::make('lead.observacoes')
                    ->title('Observações')
                    ->rows(4)
                    ->placeholder('Notas sobre o lead, histórico de contato, etc.'),
            ]),
        ];
    }

    /**
     * Save handler.
     */
    public function save(Lead $lead, Request $request)
    {
        $lead->fill($request->get('lead'))->save();

        Alert::info('Lead salvo com sucesso!');

        return redirect()->route('platform.leads.kanban');
    }

    /**
     * Delete handler.
     */
    public function remove(Lead $lead)
    {
        $lead->delete();

        Alert::warning('Lead excluído com sucesso.');

        return redirect()->route('platform.leads.kanban');
    }
}
