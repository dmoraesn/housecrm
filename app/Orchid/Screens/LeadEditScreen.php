<?php

namespace App\Orchid\Screens;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;

class LeadEditScreen extends Screen
{
    public ?Lead $lead = null;

    public function query(Lead $lead): array
    {
        $this->lead = $lead;
        return ['lead' => $lead];
    }

    public function name(): ?string
    {
        return $this->lead->exists ? 'Editar Lead: ' . $this->lead->nome : 'Novo Lead';
    }

    public function description(): ?string
    {
        return 'Gerencie informações e etapa do lead no funil.';
    }

    public function commandBar(): array
    {
        $statusBadge = $this->lead->exists ? $this->getStatusBadge($this->lead->status) : '';

        return [
            Link::make($statusBadge)
                ->class('btn btn-default')
                ->canSee($this->lead->exists)
                ->rawHtml(true),

            Button::make('Salvar')
                ->icon('check')
                ->method('save')
                ->class('btn btn-primary'),

            Button::make('Avançar Etapa')
                ->icon('arrow-right')
                ->method('avancarEtapa')
                ->class('btn btn-outline-success')
                ->canSee($this->lead->exists && $this->lead->status !== array_key_last(Lead::statusOptions())),

            Button::make('Marcar como Perdido')
                ->icon('close')
                ->method('marcarPerdido')
                ->class('btn btn-outline-danger')
                ->canSee($this->lead->exists && $this->lead->status !== 'perdido'),

            Button::make('Excluir')
                ->icon('trash')
                ->method('remove')
                ->class('btn btn-link text-danger')
                ->canSee($this->lead->exists)
                ->confirm('Tem certeza que deseja excluir este lead? Esta ação é irreversível.'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                Input::make('lead.nome')
                    ->title('Nome')
                    ->required()
                    ->placeholder('Nome completo'),

                Input::make('lead.email')
                    ->title('E-mail')
                    ->type('email'),

                Input::make('lead.telefone')
                    ->title('Telefone')
                    ->mask('(99) 99999-9999'),

                Select::make('lead.user_id')
                    ->title('Corretor Responsável')
                    ->fromModel(User::class, 'name')
                    ->empty('Sem corretor'),

                Select::make('lead.origem')
                    ->title('Origem')
                    ->options(Lead::origemOptions()) // ← CORRIGIDO
                    ->empty('Selecione uma origem'),

                Select::make('lead.status')
                    ->title('Etapa do Funil')
                    ->options(Lead::statusOptions()) // ← CORRIGIDO
                    ->required()
                    ->value(fn() => $this->lead->status ?? 'novo')
                    ->help('Define a posição do lead no processo de vendas.'),

                Input::make('lead.valor_interesse')
                    ->title('Valor de Interesse (R$)')
                    ->mask('999.999.999,99')
                    ->help('Valor aproximado do imóvel de interesse do cliente.'),

                TextArea::make('lead.mensagem')
                    ->title('Mensagem Original')
                    ->rows(2)
                    ->placeholder('Mensagem enviada pelo lead (se aplicável).')
                    ->canSee($this->lead->exists && !empty($this->lead->mensagem)),

                TextArea::make('lead.observacoes')
                    ->title('Observações Internas')
                    ->rows(5)
                    ->placeholder('Anotações importantes, histórico de contato, motivação de perda, etc.'),
            ]),
        ];
    }

    public function save(Lead $lead, Request $request)
    {
        $data = $request->validate([
            'lead.nome'           => 'required|string|max:255',
            'lead.email'          => 'nullable|email|max:255',
            'lead.telefone'       => 'nullable|string|max:20',
            'lead.user_id'        => 'nullable|exists:users,id',
            'lead.status'         => 'required|in:' . implode(',', array_keys(Lead::statusOptions())),
            'lead.origem'         => 'nullable|string|max:50',
            'lead.valor_interesse' => 'nullable|numeric',
            'lead.observacoes'    => 'nullable|string',
            'lead.mensagem'       => 'nullable|string',
        ], [
            'lead.nome.required' => 'O nome do lead é obrigatório.',
        ]);

        if (isset($data['lead']['valor_interesse'])) {
            $data['lead']['valor_interesse'] = str_replace(['.', ','], ['', '.'], $data['lead']['valor_interesse']);
        }

        $lead->fill($data['lead'])->save();

        Toast::success('Lead salvo com sucesso!');
        return redirect()->route('platform.leads.kanban');
    }

    public function avancarEtapa(Lead $lead): void
    {
        $options = Lead::statusOptions();
        $keys = array_keys($options);
        $currentIndex = array_search($lead->status, $keys);

        if ($currentIndex === false || $currentIndex === count($keys) - 1) {
            Toast::warning('O lead já está na última etapa do funil de vendas.');
            return;
        }

        $lead->status = $keys[$currentIndex + 1];
        $lead->save();

        Toast::success("Etapa avançada com sucesso para: " . $options[$lead->status]);
    }

    public function marcarPerdido(Lead $lead): void
    {
        $lead->status = 'perdido';
        $lead->observacoes = ($lead->observacoes ? $lead->observacoes . "\n\n" : '') . 
            '[Perdido] Marcado manualmente via tela de edição em ' . now()->format('d/m/Y H:i');
        $lead->save();

        Toast::success('Lead marcado como **Perdido** com sucesso.');
    }

    public function remove(Lead $lead)
    {
        $lead->delete();
        Toast::warning('Lead excluído com sucesso.');
        return redirect()->route('platform.leads.kanban');
    }

    private function getStatusBadge(string $status): string
    {
        $options = Lead::statusOptions();
        $label = $options[$status] ?? ucfirst($status);
        $color = match ($status) {
            'novo' => 'info',
            'qualificacao' => 'primary',
            'visita' => 'warning',
            'negociacao' => 'orange',
            'fechamento' => 'success',
            'perdido' => 'danger',
            default => 'secondary',
        };

        return "<span class=\"badge bg-{$color} fw-semibold\">{$label}</span>";
    }
}