<div class="bg-white rounded shadow-sm p-4 mb-3">
    <div class="mb-3">
        <h3 class="text-black fw-light">Resumo da Proposta #{{ $proposta->id }}</h3>
    </div>
    
    <hr>

    <div class="row mb-4">
        <div class="col-md-6">
            <label class="text-muted small text-uppercase">Cliente</label>
            <p class="fw-bold">{{ $proposta->lead->nome ?? '—' }}</p>
        </div>
        <div class="col-md-6">
            <label class="text-muted small text-uppercase">Data de Criação</label>
            <p class="fw-bold">{{ $proposta->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h4 class="fw-light mb-3">Financeiro</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <td>Valor do Imóvel</td>
                            <td class="text-end">R$ {{ number_format($proposta->valor_real ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Valor Avaliação</td>
                            <td class="text-end">R$ {{ number_format($proposta->valor_avaliacao ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Entrada</td>
                            <td class="text-end">R$ {{ number_format($proposta->valor_entrada ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Descontos</td>
                            <td class="text-end text-danger">- R$ {{ number_format($proposta->descontos ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr class="table-active">
                            <td><strong>Valor Financiado</strong></td>
                            <td class="text-end"><strong>R$ {{ number_format($proposta->valor_financiado ?? 0, 2, ',', '.') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    @if(!empty($proposta->description))
    <div class="mt-4">
        <h4 class="fw-light mb-2">Observações</h4>
        <div class="p-3 bg-light rounded border">
            {{ $proposta->description }}
        </div>
    </div>
    @endif
</div>