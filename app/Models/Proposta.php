<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Proposta extends Model
{
    use HasFactory, AsSource, Filterable;

    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_ATIVA    = 'ativa';
    public const STATUS_VENDIDA  = 'vendida';
    public const STATUS_CANCELADA = 'cancelada';

    protected $table = 'propostas';

    protected $fillable = [
        'lead_id',
        'fluxo_id', // <--- VÍNCULO IMPORTANTE
        'construtora_id',
        'status',
        'description',

        // Snapshot de valores (mantidos para histórico ou propostas sem fluxo)
        'valor_avaliacao',
        'valor_real',
        'valor_entrada',
        'valor_financiado',
        'valor_bonus_descontos',
        
        // Dados de parcelamento simples
        'num_parcelas',
        'valor_parcela',
        'total_parcelamento',
        
        // Legado (caso tenha dados antigos salvos direto na proposta)
        'baloes_json',
        'data_assinatura',
        'valor_assinatura_contrato',

        // CORREÇÃO APLICADA: Mapear a coluna 'order'
        'order', 
    ];

    protected $casts = [
        'lead_id'        => 'integer',
        'fluxo_id'       => 'integer',
        'construtora_id' => 'integer',
        
        'baloes_json'    => 'array',
        
        'valor_avaliacao'           => 'float',
        'valor_real'                => 'float',
        'valor_entrada'             => 'float',
        'valor_financiado'          => 'float',
        'valor_bonus_descontos'     => 'float',
        'num_parcelas'              => 'integer',
        'valor_parcela'             => 'float',
        'total_parcelamento'        => 'float',
        'valor_assinatura_contrato' => 'float',

        // CORREÇÃO APLICADA: Mapear a coluna 'order'
        'order'          => 'integer',

        'data_assinatura' => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_RASCUNHO,
        // CORREÇÃO APLICADA: Definir valor padrão para 'order'
        'order' => 0,
    ];

    // ===================================================================
    // RELACIONAMENTOS
    // ===================================================================

    /**
     * O Fluxo Financeiro que originou esta proposta.
     * É daqui que tiraremos os dados detalhados (balões, chaves, cartório).
     */
    public function fluxo(): BelongsTo
    {
        return $this->belongsTo(Fluxo::class, 'fluxo_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function construtora(): BelongsTo
    {
        return $this->belongsTo(Construtora::class, 'construtora_id');
    }

    // ===================================================================
    // SCOPES
    // ===================================================================

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ATIVA);
    }
}