@php
    $hasProposal = $lead->propostas->isNotEmpty();
    $hasContract = $lead->contratos->where('status', 'ativo')->isNotEmpty();
    $isCold      = in_array($status ?? '', ['novo', 'perdido']);
    $phoneClean  = preg_replace('/\D/', '', $lead->telefone ?? '');
    $borderColor = $columnColor ?? '#6c757d';

    $icons = [
        'eye'      => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>',
        'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-whatsapp" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z"/><path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9"/></svg>',
        'proposal' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-plus" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M12 11v6"/><path d="M9 14h6"/></svg>',
        'contract' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-check" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M9 15l2 2l4 -4"/></svg>',
        'ice'      => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-snowflake" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z"/><path d="M10 4l2 1l2 -1"/><path d="M12 2v6.5l3 .5"/><path d="M17 8l-1 2l1 2"/><path d="M19 12h-6.5l-.5 3"/><path d="M14 16l-2 1l-2 -1"/><path d="M12 18v-6.5l-3 -.5"/><path d="M7 8l1 2l-1 2"/><path d="M5 12h6.5l.5 -3"/></svg>',
    ];
@endphp

<article class="kanban-card border-start border-4 mb-2"
         data-id="{{ $lead->id }}"
         data-status="{{ $status ?? '' }}"
         style="border-color: {{ $borderColor }};">
    <header class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <strong>{{ $lead->nome }}</strong>
            <small>{{ $lead->telefone_formatado ?? $lead->telefone }}</small>
        </div>
    </header>

    <div class="mb-2">
        <span class="badge">
            {{ $lead->corretor?->name ?? 'Sem corretor' }}
        </span>
    </div>

    @if($lead->valor_interesse > 0)
        <div class="mb-2 text-end">
            <small class="text-success fw-semibold">
                R$ {{ number_format($lead->valor_interesse, 0, ',', '.') }}
            </small>
        </div>
    @endif

    @if($lead->mensagem)
        <div class="mb-2 text-muted small fst-italic text-truncate">
            "{{ Str::limit($lead->mensagem, 60) }}"
        </div>
    @endif

    <footer class="d-flex gap-2 justify-content-end">
        <a href="{{ route('platform.leads.edit', $lead) }}" class="kanban-action" title="Ver detalhes">
            {!! $icons['eye'] !!}
        </a>

        @if($phoneClean)
            <a href="https://wa.me/55{{ $phoneClean }}?text=Olá%20{{ urlencode($lead->nome) }}" target="_blank" class="kanban-action text-success" title="WhatsApp">
                {!! $icons['whatsapp'] !!}
            </a>
        @endif

        <a href="{{ route('platform.propostas.create.from.lead', $lead) }}"
           class="kanban-action {{ $hasProposal ? 'has-proposal' : '' }}"
           title="{{ $hasProposal ? 'Editar proposta' : 'Criar proposta' }}">
            {!! $icons['proposal'] !!}
        </a>

        @if($hasContract)
            <a href="#" class="kanban-action text-success" title="Contrato ativo">
                {!! $icons['contract'] !!}
            </a>
        @endif

        @if($isCold)
            <span class="kanban-action text-info" title="{{ $status == 'novo' ? 'Lead novo' : 'Lead perdido' }}">
                {!! $icons['ice'] !!}
            </span>
        @endif
    </footer>
</article>