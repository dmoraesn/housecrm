<?php

namespace App\Orchid\Screens;

use App\Models\AITemplate;
use App\Models\LeadStatus;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class AITemplateEditScreen extends Screen
{
    public $template;

    /**
     * Carrega o template (novo ou existente)
     */
    public function query(AITemplate $template): iterable
    {
        $this->template = $template->exists ? $template : new AITemplate();

        return [
            'template' => $this->template,
        ];
    }

    public function name(): ?string
    {
        return $this->template->exists ? 'Editar Prompt de IA' : 'Criar Novo Prompt';
    }

    public function description(): ?string
    {
        return 'Configure o prompt usado automaticamente em cada etapa do funil de leads';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Salvar')
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make('Cancelar')
                ->icon('bs.x-circle')
                ->route('platform.config.prompts.index'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([

                Select::make('template.lead_status')
                    ->title('Etapa do Lead')
                    ->options(
                        collect(LeadStatus::cases())->mapWithKeys(fn ($status) => [
                            $status->value => ucfirst(str_replace('_', ' ', $status->value))
                        ])->toArray()
                    )
                    ->required()
                    ->help('Selecione em qual etapa do funil este prompt será usado'),

                Input::make('template.nome')
                    ->title('Nome do Prompt')
                    ->placeholder('Ex: Follow-up Qualificação')
                    ->required(),

                TextArea::make('template.prompt')
                    ->title('Prompt (texto enviado à IA)')
                    ->rows(12)
                    ->placeholder('Ex: Você é um corretor de imóveis experiente e simpático...')
                    ->required()
                    ->help('Use placeholders como {nome}, {gancho_selecionado}, {valor_interesse}, {observacoes}'),

                Input::make('template.max_tokens')
                    ->title('Máximo de Tokens (aprox. palavras)')
                    ->type('number')
                    ->min(50)
                    ->max(800)
                    ->value($this->template->max_tokens ?? 120)
                    ->required()
                    ->help('Recomendado: 100-150 para mensagens curtas'),

                CheckBox::make('template.ativo')
                    ->title('Ativo')
                    ->sendTrueOrFalse()
                    ->help('Desativado = usa o prompt padrão interno (fallback)'),
            ]),
        ];
    }

    /**
     * Salva o prompt
     */
    public function save(AITemplate $template, Request $request)
    {
        $data = $request->get('template');

        // Garante que ativo seja booleano
        $data['ativo'] = isset($data['ativo']) && $data['ativo'];

        $template->fill($data)->save();

        Alert::success('Prompt salvo com sucesso!');

        return redirect()->route('platform.config.prompts.index');
    }
}