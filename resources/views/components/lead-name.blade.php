<div class="d-flex align-items-center gap-2">
    <strong>{{ $lead->nome }}</strong>
    @if($lead->propostas->count() > 0)
        <span class="badge bg-info text-dark" title="{{ $lead->propostas->count() }} proposta(s)">
            <i class="bi bi-file-earmark-text"></i>
        </span>
    @endif
</div>