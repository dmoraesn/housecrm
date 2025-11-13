<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable; // ← ADICIONE

class Proposta extends Model
{
    use AsSource, Filterable; // ← ADICIONE Filterable

    protected $table = 'propostas';

    protected $fillable = [
        'lead_id',
        'fluxo_id',
        'valor_real',
        'valor_entrada',
        'valor_restante',
        'status',
    ];

    protected $casts = [
        'valor_real'      => 'decimal:2',
        'valor_entrada'   => 'decimal:2',
        'valor_restante'  => 'decimal:2',
    ];

    // Relacionamentos
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function fluxo(): BelongsTo
    {
        return $this->belongsTo(Fluxo::class, 'fluxo_id');
    }

    // Permite filtros no Orchid
    protected $allowedFilters = [
        'lead.nome',
        'fluxo.valor_assinatura_contrato_data',
    ];

    protected $allowedSorts = [
        'id',
        'lead.nome',
        'fluxo.valor_assinatura_contrato_data',
    ];
}