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
use Orchid\Screen\Fields\DateTimer;
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
        // CRIA LEAD VAZIO COM VALORES PADRÃO
        if (!$lead->exists) {
            $lead = new Lead();
            $lead->fill([
                'status' => LeadStatus::NOVO->value,
                'origem' => null,
                'data_contato' => null,
                'valor_interesse' => null,
                'observacoes' => null,
                'mensagem' => null,
                'telefone' => null,
                'email' => null,
                'nome' => null,
                'user_id' => null,
            ]);
        }

        // Garante que o valor do Enum seja passado como string para o binding do Orchid
        $lead->status = $lead->status instanceof LeadStatus ? $lead->status->value : $lead->status;
        $lead->origem = $lead->origem instanceof LeadOrigem ? $lead->origem->value : $lead->origem;

        $this->lead = $lead->exists ? $lead->load('corretor') : $lead;

        $this->corretorOptions = User::whereHas('roles', fn($q) => $q->where('slug', 'corretor'))
            ->pluck('name', 'id')
            ->toArray();

        $this->statusOptions = Lead::statusOptions();

        // OTIMIZAÇÃO: Agora usa o método label() definido em App\Enums\LeadOrigem
        $this->origemOptions = collect(LeadOrigem::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();

        return [
            'lead' => $this->lead,
        ];
    }

    public function name(): ?string
    {
        return $this->lead->exists ? 'Editar Lead: ' . ($this->lead->nome ?? 'Sem nome') : 'Criar Novo Lead';
    }

    public function description(): string
    {
        return 'Gerencie detalhes, status e atribuição do lead.';
    }

    public function commandBar(): array
    {
        $statusValue = $this->lead->status ?? 'novo';

        return [
            Button::make('Salvar')
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make('Avançar Etapa')
                ->icon('bs.arrow-right')
                ->method('avancar')
                ->canSee(
                    $this->lead->exists &&
                    !in_array($statusValue, ['fechamento', 'perdido'])
                ),

            Button::make('Marcar como Perdido')
                ->icon('bs.x-circle')
                ->method('perdido')
                ->confirm('Tem certeza? Isso irá marcar o lead como perdido e tirá-lo do funil de vendas.')
                ->canSee($this->lead->exists && $statusValue !== 'perdido'),

            Button::make('Remover')
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
                    ->empty('Selecione uma origem')
                    ->help('De onde veio este lead'),
            ])->title('Informações do Contato'),

            Layout::rows([
                Select::make('lead.status')
                    ->options($this->statusOptions)
                    ->title('Status no Funil')
                    ->help('Etapa atual do lead no seu fluxo de vendas.')
                    ->required(),

                Select::make('lead.user_id')
                    ->fromModel(User::whereHas('roles', fn($q) => $q->where('slug', 'corretor')), 'name', 'id')
                    ->title('Corretor Responsável')
                    ->empty('Sem corretor atribuído'),

                DateTimer::make('lead.data_contato')
                    ->title('Data do Contato')
                    ->format('Y-m-d\TH:i')
                    ->allowInput()
                    ->placeholder('Data e hora do contato'),

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
            'nome'              => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:leads,email,' . ($this->lead->id ?? 'NULL'),
            'telefone'          => 'nullable|string|max:20',
            'origem'            => 'nullable|in:' . implode(',', array_keys($this->origemOptions)),
            'mensagem'          => 'nullable|string',
            'valor_interesse'=> 'nullable|numeric|min:0',
            'status'            => 'required|in:' . implode(',', array_keys($this->statusOptions)),
            'user_id'           => 'nullable|exists:users,id',
            'data_contato'      => 'nullable|date',
            'observacoes'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                Toast::error($error);
            }
            return back()->withInput();
        }

        if (!empty($data['valor_interesse'])) {
            $data['valor_interesse'] = str_replace(['.', ' '], '', $data['valor_interesse']);
            $data['valor_interesse'] = str_replace(',', '.', $data['valor_interesse']);
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

    public function remove()
    {
        $this->lead->delete();
        Toast::info('Lead removido permanentemente.');
        return redirect()->route('platform.leads.index');
    }
}