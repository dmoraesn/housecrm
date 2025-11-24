<?php
declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Lead;
use App\Models\User;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Toast;

class LeadListScreen extends Screen
{
    public function query(): array
    {
        $leads = Lead::with('corretor')
            ->when(request('status'), fn($q) =>
                $q->where('status', request('status'))
            )
            ->when(request('origem'), fn($q) =>
                $q->where('origem', request('origem'))
            )
            ->when(request('nome'), fn($q) =>
                $q->where('nome', 'like', '%' . request('nome') . '%')
            )
            ->when(request('email'), fn($q) =>
                $q->where('email', 'like', '%' . request('email') . '%')
            )
            ->orderBy('created_at', 'desc')
            ->paginate();

        return [
            'leads' => $leads,
        ];
    }

    public function name(): ?string
    {
        return 'Leads';
    }

    public function commandBar(): array
    {
        return [
            Link::make('Criar Lead')
                ->icon('plus')
                ->route('platform.leads.create'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                Select::make('status')
                    ->options(Lead::statusOptions())
                    ->title('Status')
                    ->empty('Todos os Status'),

                Select::make('origem')
                    ->options(Lead::origemOptions())
                    ->title('Origem')
                    ->empty('Todas as Origens'),

                Input::make('nome')
                    ->title('Nome')
                    ->placeholder('Buscar por nome'),

                Input::make('email')
                    ->title('Email')
                    ->placeholder('Buscar por email'),
            ]),

            Layout::table('leads', [
                TD::make('id', 'ID')
                    ->sort(),

                TD::make('nome', 'Nome')
                    ->render(fn(Lead $lead) =>
                        Link::make($lead->nome)
                            ->route('platform.leads.edit', $lead->id)
                    ),

                TD::make('email', 'Email'),

                TD::make('telefone_formatado', 'Telefone'),

                TD::make('origem', 'Origem')
                    ->render(fn(Lead $lead) => ucfirst($lead->origem?->value ?? '')),

                TD::make('status', 'Status')
                    ->render(fn(Lead $lead) => $lead->status_badge)
                    ->width('180px'),

                TD::make('corretor.name', 'Corretor')
                    ->render(fn(Lead $lead) => $lead->corretor?->name ?? 'Sem corretor'),

                TD::make('created_at', 'Criado em')
                    ->render(fn(Lead $lead) =>
                        $lead->created_at?->format('d/m/Y')
                    ),

                TD::make('Ações')
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn(Lead $lead) =>
                        DropDown::make()
                            ->icon('more-horizontal')
                            ->list([
                                Link::make('Editar')
                                    ->icon('pencil')
                                    ->route('platform.leads.edit', $lead->id),

                                Button::make('Excluir')
                                    ->icon('trash')
                                    ->confirm('Confirma exclusão?')
                                    ->method('remove', ['id' => $lead->id]),
                            ])
                    ),
            ]),
        ];
    }

    public function remove($id)
    {
        Lead::findOrFail($id)->delete();

        Toast::success('Lead removido com sucesso.');
    }
}