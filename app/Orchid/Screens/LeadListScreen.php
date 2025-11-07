<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\TD;
use App\Models\Lead;
use Illuminate\Support\Str;

class LeadListScreen extends Screen
{
    public $name = 'Leads';
    public $description = 'Gerencie seus leads e oportunidades';

    public function query(): array
    {
        return [
            'leads' => Lead::orderByDesc('id')->paginate(10),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Novo Lead')
                ->icon('bs.plus-circle')
                ->route('platform.leads.create'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('leads', [
                TD::make('id', 'ID')
                    ->render(fn (Lead $lead) => $lead->id)
                    ->sort(),

                TD::make('nome', 'Nome')
                    ->render(fn (Lead $lead) => e($lead->nome)),

                TD::make('email', 'E-mail')
                    ->render(fn (Lead $lead) => e($lead->email)),

                TD::make('telefone', 'Telefone')
                    ->render(fn (Lead $lead) => e($lead->telefone)),

                TD::make('status', 'Status')
                    ->render(fn (Lead $lead) => ucfirst(str_replace('_', ' ', $lead->status))),

                TD::make('origem', 'Origem')
                    ->render(fn (Lead $lead) => e($lead->origem)),

                TD::make('mensagem', 'Mensagem')
                    ->render(fn (Lead $lead) => Str::limit(e($lead->mensagem), 40)),

                TD::make('Ações')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn (Lead $lead) =>
                        Link::make('Editar')
                            ->route('platform.leads.edit', $lead->id)
                            ->icon('bs.pencil')
                    ),
            ]),
        ];
    }
}
