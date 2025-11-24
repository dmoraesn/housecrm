<?php

namespace App\Orchid\Screens;

use App\Models\AITemplate;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;

class AITemplateListScreen extends Screen
{
    /**
     * Query data.
     */
    public function query(): iterable
    {
        return [
            'templates' => AITemplate::orderBy('lead_status')
                ->paginate(15),
        ];
    }

    /**
     * Display header name.
     */
    public function name(): ?string
    {
        return 'Prompts de IA';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Configure mensagens automáticas personalizadas para cada etapa do funil de leads';
    }

    /**
     * Button commands.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Criar novo prompt')
                ->icon('bs.plus-circle')
                ->route('platform.config.prompts.create')
                ->title('Adicionar novo prompt personalizado'),
        ];
    }

    /**
     * Views.
     */
    public function layout(): iterable
    {
        return [
            Layout::table('templates', [
                TD::make('nome', 'Nome do Prompt')
                    ->sort()
                    ->width('180px')
                    ->render(fn (AITemplate $t) => '<strong>' . e($t->nome) . '</strong>'),

                TD::make('lead_status', 'Etapa')
                    ->sort()
                    ->width('120px')
                    ->render(fn (AITemplate $t) => '<span class="badge bg-primary rounded-pill">' .
                        ucfirst(str_replace('_', ' ', $t->lead_status)) .
                        '</span>'),

                TD::make('max_tokens', 'Tokens')
                    ->sort()
                    ->alignCenter()
                    ->width('90px')
                    ->render(fn (AITemplate $t) => '<small class="text-muted">' . $t->max_tokens . '</small>'),

                TD::make('prompt', 'Prompt')
                    ->render(fn (AITemplate $template) => view('platform.prompt-preview', [
                        'template' => $template,
                    ])),

                TD::make('ativo', 'Status')
                    ->alignCenter()
                    ->width('100px')
                    ->render(fn (AITemplate $t) => $t->ativo
                        ? '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Ativo</span>'
                        : '<span class="text-secondary"><i class="bi bi-slash-circle"></i> Inativo</span>'
                    ),

                TD::make('Ações')
                    ->alignCenter()
                    ->width('80px')
                    ->render(fn (AITemplate $t) => Link::make()
                        ->icon('bs.pencil')
                        ->title('Editar')
                        ->route('platform.config.prompts.edit', $t->id)
                    ),
            ]),
        ];
    }
}