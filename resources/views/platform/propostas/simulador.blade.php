@php
/**
 * View: resources/views/platform/propostas/simulador.blade.php
 * Finalidade: Informar que a criação de Propostas foi movida para o fluxo de Leads.
 */
@endphp

<div class="alert alert-info d-flex align-items-center p-4 shadow-sm border-0" role="alert">
    <i class="bi bi-info-circle-fill me-3 h4 mb-0 text-info"></i>
    <div>
        <h4 class="alert-heading fw-bold mb-1">Criação de Propostas Movida</h4>
        <p class="mb-2">
            A criação e simulação de propostas agora é feita **diretamente no fluxo do Lead** para garantir o vínculo correto.
        </p>
        <a href="{{ url('/admin/fluxo') }}" class="btn btn-sm btn-info">
            <i class="bi bi-arrow-right-circle me-1"></i> Acessar Fluxos
        </a>
    </div>
</div>