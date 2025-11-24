<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Lead;
use App\Models\User;
use App\Enums\LeadOrigem;
use App\Enums\LeadStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LeadEditScreen extends Screen
{
    public $lead;
    public array $corretorOptions = [];
    public array $statusOptions = [];
    public array $origemOptions = [];

    public function query(Lead $lead): array
    {
        $lead->mergeCasts([
            'status' => 'string',
            'origem' => 'string',
        ]);

        $this->lead = $lead->exists ? $lead : new Lead();

        if ($this->lead->exists) {
            $raw = $this->lead->getRawOriginal();
            $this->lead->setRawAttributes($raw, true);
        }

        $this->corretorOptions = User::whereHas('roles', fn ($q) =>
            $q->where('slug', 'corretor')
        )->pluck('name', 'id')->toArray();

        $this->statusOptions = Lead::statusOptions();

        $this->origemOptions = collect(LeadOrigem::cases())
            ->mapWithKeys(fn ($case) => [$case->value => ucfirst($case->value)])
            ->toArray();

        return [
            'lead' => $this->lead,
        ];
    }

    public function name(): ?string
    {
        return $this->lead->exists ? 'Editar Lead: ' . $this->lead->nome : 'Criar Novo Lead';
    }

    public function description(): string
    {
        return 'Gerencie detalhes, status e atribuição do lead.';
    }

    public function commandBar(): array
    {
        // 🔧 Correção cirúrgica: garantir que status seja string ou enum de forma segura
        $status = $this->lead->status;
        $statusValue = $status instanceof LeadStatus ? $status->value : $status;

        return [
            Button::make(__('Salvar'))
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make('Avançar Etapa')
                ->icon('bs.arrow-right')
                ->method('avancar')
                ->canSee(
                    $this->lead->exists &&
                    $statusValue !== LeadStatus::FECHAMENTO->value &&
                    $statusValue !== LeadStatus::PERDIDO->value
                ),

            Button::make('Marcar como Perdido')
                ->icon('bs.x-circle')
                ->method('perdido')
                ->confirm('Tem certeza? Isso irá marcar o lead como perdido e tirá-lo do funil de vendas.')
                ->canSee(
                    $this->lead->exists &&
                    $statusValue !== LeadStatus::PERDIDO->value
                ),

            Button::make(__('Remover'))
                ->icon('bs.trash')
                ->method('remove')
                ->confirm('Tem certeza de que deseja excluir este Lead?')
                ->canSee($this->lead->exists),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                Input::make('lead.nome')
                    ->title('Nome')
                    ->placeholder('Nome Completo')
                    ->required(),

                Input::make('lead.email')
                    ->title('E-mail')
                    ->type('email')
                    ->placeholder('exemplo@email.com')
                    ->required(),

                Input::make('lead.telefone')
                    ->title('Telefone')
                    ->mask('(99) 99999-9999')
                    ->placeholder('(XX) XXXXX-XXXX'),

                Input::make('lead.valor_interesse')
                    ->title('Valor de Interesse (R$)')
                    ->type('number')
                    ->step('0.01')
                    ->placeholder('0.00'),

                TextArea::make('lead.mensagem')
                    ->title('Mensagem Original do Lead')
                    ->rows(3)
                    ->placeholder('Mensagem enviada pelo lead na captação (se houver).'),

                Select::make('lead.origem')
                    ->options($this->origemOptions)
                    ->title('Origem')
                    ->empty('Selecione uma origem'),
            ])->title('Informações do Contato'),

            Layout::rows([
                Select::make('lead.status')
                    ->options($this->statusOptions)
                    ->title('Status no Funil')
                    ->help('Etapa atual do lead no seu fluxo de vendas.')
                    ->required(),

                Select::make('lead.user_id')
                    ->options($this->corretorOptions)
                    ->title('Corretor Responsável')
                    ->empty('Sem corretor atribuído'),

                Input::make('lead.data_contato')
                    ->title('Data do Contato')
                    ->type('datetime-local')
                    ->help('Data/Hora do primeiro contato ou última interação.'),

                TextArea::make('lead.observacoes')
                    ->title('Observações Internas')
                    ->rows(5)
                    ->placeholder('Notas sobre o cliente, necessidades e histórico.'),
            ])->title('Gestão e Funil'),
        ];
    }

    public function save(Request $request)
    {
        $data = $request->get('lead');

        $validator = Validator::make($data, [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:leads,email,' . ($this->lead->exists ? $this->lead->id : 'NULL'),
            'telefone' => 'nullable|string|max:20',
            'origem' => 'nullable|string|max:255',
            'mensagem' => 'nullable|string',
            'valor_interesse' => 'nullable|numeric|min:0',
            'status' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'data_contato' => 'nullable|date',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                Toast::error($error);
            }
            return back()->withInput();
        }

        $this->lead->fill($data)->save();

        Toast::info('Lead salvo com sucesso!');

        return redirect()->route('platform.leads.edit', $this->lead);
    }

    public function avancar()
    {
        if ($this->lead->avancarEtapa()) {
            Toast::success("Etapa avançada para: {$this->lead->status_label}.");
        } else {
            Toast::error('Não é possível avançar a etapa.');
        }

        return redirect()->route('platform.leads.edit', $this->lead);
    }

    public function perdido(Request $request)
    {
        $motivo = $request->input('motivo', '');

        $this->lead->marcarComoPerdido($motivo);

        Toast::warning('Lead marcado como perdido e removido do funil.');

        return redirect()->route('platform.leads.index');
    }

    public function remove(Lead $lead)
    {
        $lead->delete();

        Toast::info('Lead removido permanentemente.');

        return redirect()->route('platform.leads.list');
    }
}
