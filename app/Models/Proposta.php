<?php

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

    // ===================================================================
    // PROPRIEDADES DO MODELO
    // ===================================================================

    /**
     * Tabela associada ao modelo.
     */
    protected $table = 'propostas';

    /**
     * Chave primária.
     */
    protected $primaryKey = 'id';

    /**
     * Atributos que podem ser atribuídos em massa.
     * Incluído 'construtora_id' e 'data_assinatura' para sincronia com FluxoScreen.
     */
    protected $fillable = [
        // Relacionamentos
        'lead_id',
        'construtora_id', // Novo: Usado em FluxoScreen

        // Status
        'status',

        // Inputs e Valores de Referência
        'valor_avaliacao',
        'valor_real',
        'valor_financiado',
        'valor_bonus_descontos', // Alterado: De 'descontos' para 'valor_bonus_descontos' (como no FluxoScreen)
        'valor_assinatura_contrato', // Alterado: De 'valor_assinatura' para 'valor_assinatura_contrato'
        'data_assinatura', // NOVO CRÍTICO: Necessário para a PropostasListScreen
        'valor_parcela',
        'num_parcelas', // Mantido 'num_parcelas' por enquanto

        // Campos Calculados
        'valor_entrada',
        'total_parcelamento',
        'valor_restante',
        'baloes_json',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos (Casts).
     */
    protected $casts = [
        // JSON
        'baloes_json' => 'array',

        // Inteiros
        'lead_id'           => 'integer',
        'construtora_id'    => 'integer',
        'num_parcelas'      => 'integer',

        // Decimais (float/moeda)
        'valor_avaliacao'           => 'float',
        'valor_real'                => 'float',
        'valor_financiado'          => 'float',
        'valor_bonus_descontos'     => 'float',
        'valor_assinatura_contrato' => 'float',
        'valor_parcela'             => 'float',
        'valor_entrada'             => 'float',
        'total_parcelamento'        => 'float',
        'valor_restante'            => 'float',

        // Datas
        'data_assinatura'   => 'date', // CORREÇÃO CRÍTICA
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Valores padrão para atributos.
     */
    protected $attributes = [
        'status' => 'rascunho',
        'valor_bonus_descontos' => 0.0,
        'valor_entrada' => 0.0,
        'total_parcelamento' => 0.0,
        'valor_restante' => 0.0,
        'baloes_json' => '[]',
    ];

    // ===================================================================
    // RELACIONAMENTOS
    // ===================================================================

    /**
     * Lead associado à proposta.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Lead::class, 'lead_id');
    }
    
    /**
     * Construtora associada à proposta (adicionado para consistência).
     */
    public function construtora(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Construtora::class, 'construtora_id');
    }

    // ===================================================================
    // SCOPES (FILTROS ÚTEIS)
    // ===================================================================

    /**
     * Scope: Propostas com diferença zero (equilibradas).
     */
    public function scopeEquilibradas(Builder $query): Builder
    {
        // whereRaw é a forma correta de comparar floats (usando margem de erro)
        return $query->whereRaw('ABS(valor_restante) < 0.01');
    }

    /**
     * Scope: Propostas com entrada maior que o valor fornecido.
     */
    public function scopeEntradaAcimaDe(Builder $query, float $valor): Builder
    {
        return $query->where('valor_entrada', '>=', $valor);
    }
}
// 127 linhas mantidas