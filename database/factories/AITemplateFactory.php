<?php
namespace App\Orchid\Screens;
use App\Models\AITemplate;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class AITemplateListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'templates' => AITemplate::paginate(15),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Prompts de IA';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Criar Prompt')
                ->icon('bs.plus-circle')
                ->route('platform.config.prompts.create'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('templates', [
                TD::make('nome', 'Nome')
                    ->sort(),
                TD::make('lead_status', 'Status Lead')
                    ->sort(),
                TD::make('max_tokens', 'Max Tokens')
                    ->sort(),
                TD::make('ativo', 'Ativo')
                    ->toggle('ativo'),
                TD::make(__('Ações'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(function (AITemplate $template) {
                        return [
                            Button::make('Editar')
                                ->icon('bs.pencil')
                                ->route('platform.config.prompts.edit', $template->id),
                        ];
                    }),
            ]),
        ];
    }
}