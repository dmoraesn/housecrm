<?php

namespace App\Orchid\Screens;

use App\Models\Lead;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class LeadListScreen extends Screen
{
    public $name = 'Leads';
    public $description = 'Gerenciamento de todos os leads';

    public function query(): array
    {
        $query = Lead::query()
            ->when(request('nome'), fn($q, $v) => $q->where('nome', 'like', "%{$v}%"))
            ->when(request('email'), fn($q, $v) => $q->where('email', 'like', "%{$v}%"))
            ->when(request('telefone'), fn($q, $v) => $q->where('telefone', 'like', "%{$v}%"))
            ->when(request('status'), fn($q, $v) => $q->where('status', $v))
            ->when(request('origem'), fn($q, $v) => $q->where('origem', $v))
            ->when(request('corretor_name'), fn($q, $v) =>
                $q->whereHas('corretor', fn($qq) => $qq->where('name', 'like', "%{$v}%"))
            )
            ->orderByDesc('created_at') // ✔️ ordenação padrão válida
            ->with(['corretor', 'propostas']);

        return [
            'leads' => $query->paginate(15),
            'statusOptions' => Lead::statusOptions(),
            'origemOptions' => Lead::origemOptions(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Novo Lead')
                ->icon('bs.plus-circle')
                ->route('platform.leads.create'),

            Link::make('Kanban')
                ->icon('bs.grid-3x3-gap')
                ->route('platform.leads.kanban'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('leads', [
                TD::make('id', 'ID')
                    ->width('60px')
                    ->sort()
                    ->render(fn(Lead $lead) =>
                        Link::make("#{$lead->id}")
                            ->route('platform.leads.edit', $lead)
                            ->class('text-primary fw-bold')
                    ),

                TD::make('nome', 'Nome')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn(Lead $lead) =>
                        Link::make($lead->nome)
                            ->route('platform.leads.edit', $lead)
                            ->class('fw-bold text-primary')
                    ),

                TD::make('email', 'E-mail')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn(Lead $lead) => $this->formatEmail($lead->email)),

                TD::make('telefone', 'Telefone')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn(Lead $lead) => $this->formatPhone($lead->telefone)),

                TD::make('origem', 'Origem')
                    ->sort()
                    ->filter(TD::FILTER_SELECT, Lead::origemOptions())
                    ->render(fn(Lead $lead) =>
                        $lead->origem ?? '<em class="text-muted">N/D</em>'
                    ),

                TD::make('status', 'Status')
                    ->sort()
                    ->filter(TD::FILTER_SELECT, Lead::statusOptions())
                    ->render(fn(Lead $lead) =>
                        view('components.status-badge', ['status' => $lead->status])
                    ),

                TD::make('corretor.name', 'Corretor')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn(Lead $lead) =>
                        $lead->corretor?->name ?? '<em class="text-muted">N/D</em>'
                    )
                    ->cantHide(),

                TD::make('created_at', 'Criado em')
                    ->sort()
                    ->render(fn(Lead $lead) =>
                        $lead->created_at->format('d/m/Y H:i')
                    )
                    ->defaultHidden(),
            ]),
        ];
    }

    private function formatEmail(?string $email): string
    {
        return $email
            ? sprintf(
                '<a href="mailto:%s" class="text-decoration-none">%s</a>',
                e($email),
                e($email)
            )
            : '<em class="text-muted">Sem e-mail</em>';
    }

    private function formatPhone(?string $phone): string
    {
        if (!$phone) {
            return '<em class="text-muted">Sem telefone</em>';
        }

        $clean = preg_replace('/\D/', '', $phone);
        $whatsapp = "https://wa.me/55{$clean}";

        return sprintf(
            '<a href="%s" target="_blank" class="text-success text-decoration-none">
                WhatsApp %s
            </a>',
            $whatsapp,
            e($phone)
        );
    }
}
