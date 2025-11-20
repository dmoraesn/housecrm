<?php

namespace App\Orchid\Screens;

use App\Models\Lead;
use App\Models\User;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Color;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast; // Adicionado para correção do erro Call to undefined function

class LeadEditScreen extends Screen
{
    public $lead;

    /**
     * Query data.
     */
    public function query(Lead $lead): iterable
    {
        return [
            'lead' => $lead,
        ];
    }

    /**
     * Screen name.
     */
    public function name(): ?string
    {
        return 'Editar Lead: ' . $this->lead->nome;
    }

    /**
     * Description (aparece abaixo do título).
     */
    public function description(): ?string
    {
        return 'Gerencie informações e etapa do lead no funil.';
    }

    /**
     * Badge do status do lead.
     */
    public function badge(): ?array
    {
        $statusMap = [
            'novo' => Color::INFO(),          // Novo Lead (Azul Claro)
            'qualificacao' => Color::PRIMARY(),  // Qualificação (Azul)
            'visita' => Color::WARNING(),     // Visita Marcada (Amarelo)
            'negociacao' => Color::WARNING(), // Negociação (Amarelo/Laranja - Cor de atenção)
            'fechamento' => Color::SUCCESS(),    // Fechamento (Verde)
            'perdido' => Color::DANGER(),        // Perdido (Vermelho)
        ];

        $statusKey = $this->lead->status;
        $color = $statusMap[$statusKey] ?? Color::SECONDARY(); // Cor padrão

        return [
            $this->lead->status_label => $color,
        ];
    }

    /**
     * Command bar.
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Salvar')
                ->method('save')
                ->type(Color::PRIMARY())
                ->icon('check'),



            Button::make('Excluir')
                ->method('remove')
                ->type(Color::DANGER())
                ->icon('trash'),
        ];
    }

    /**
     * Form layout.
     */
    public function layout(): iterable
    {
        return [
            Layout::rows([

                Input::make('lead.nome')
                    ->title('Nome')
                    ->required(),

                Input::make('lead.email')
                    ->title('E-mail')
                    ->type('email'),

                Input::make('lead.telefone')
                    ->title('Telefone')
                    ->mask('(99) 99999-9999'),

                Select::make('lead.user_id')
                    ->title('Corretor Responsável')
                    ->options(User::pluck('name', 'id')->toArray())
                    ->empty('Sem corretor'),

                Select::make('lead.origem')
                    ->title('Origem')
                    ->options([
                        'Site' => 'Site',
                        'Instagram' => 'Instagram',
                        'Facebook' => 'Facebook',
                        'Indicação' => 'Indicação',
                        'Anúncio' => 'Anúncio',
                        'WhatsApp' => 'WhatsApp',
                        'Google' => 'Google',
                        'Email' => 'Email',
                        'Telefone' => 'Telefone',
                        'Evento' => 'Evento',
                        'Outro' => 'Outro',
                    ])
                    ->empty('Selecione uma origem'),

                Select::make('lead.status')
                    ->title('Etapa do Funil')
                    ->required()
                    ->options([
                        'novo' => '1 Novo Lead / Descoberta',
                        'qualificacao' => '2 Qualificação / Entendimento',
                        'visita' => '3 Apresentação / Visita',
                        'negociacao' => '4 Proposta / Negociação',
                        'fechamento' => '5 Fechamento / Contrato',
                        'perdido' => '6 Perdido',
                    ]),

                Input::make('lead.valor_interesse')
                    ->title('Valor de Interesse (R$)')
                    ->mask([
                        'alias' => 'currency',
                        'prefix' => 'R$ ',
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'digits' => 2,
                        'autoGroup' => true,
                    ]),

                Input::make('lead.observacoes')
                    ->title('Observações Internas')
                    ->rows(5)
                    ->type('textarea'),

            ]),
        ];
    }

    /**
     * Salvar lead.
     */
    public function save(Request $request, Lead $lead)
    {
        $lead->update($request->get('lead'));
        Toast::success('Lead atualizado com sucesso!');
    }

    /**
     * Avançar etapa.
     */
    public function avancarEtapa(Lead $lead)
    {
        $pipeline = [
            'novo' => 'qualificacao',
            'qualificacao' => 'visita',
            'visita' => 'negociacao',
            'negociacao' => 'fechamento',
        ];

        if (isset($pipeline[$lead->status])) {
            $lead->status = $pipeline[$lead->status];
            $lead->save();
            Toast::success('Etapa avançada!');
        } else {
            Toast::warning('Este lead já está na última etapa.');
        }
    }

    /**
     * Marcar como perdido.
     */
    public function marcarPerdido(Lead $lead)
    {
        $lead->status = 'perdido';
        $lead->save();
        Toast::info('Lead marcado como perdido.');
    }

    /**
     * Remover lead.
     */
    public function remove(Lead $lead)
    {
        $lead->delete();
        Toast::error('Lead excluído.');
        return redirect()->route('platform.leads');
    }
}