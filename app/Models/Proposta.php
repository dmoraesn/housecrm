<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable; // Adicionado para a listagem

class Proposta extends Model
{
    use HasFactory, AsSource, Filterable; // Adicionado Filterable

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
     */
    protected $fillable = [
        // Relacionamento
        'lead_id',

        // Status da proposta
        'status',

        // Campos do simulador (inputs visíveis)
        'valor_avaliacao',
        'valor_real',
        'valor_financiado',
        'descontos',
        'valor_assinatura',
        'valor_parcela',
        'num_parcelas',

        // Campos calculados pelo frontend (JS)
        'valor_entrada',
        'total_parcelamento',
        'valor_restante',
        'baloes_json',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     * Esta é a forma correta de lidar com os dados do simulador.
     */
    protected $casts = [
        // JSON
        'baloes_json' => 'array',

        // Inteiros
        'lead_id'     => 'integer',
        'num_parcelas'=> 'integer',

        // Decimais (moeda)
        'valor_avaliacao'    => 'float',
        'valor_real'         => 'float',
        'valor_financiado'   => 'float',
        'descontos'          => 'float',
        'valor_assinatura'   => 'float',
        'valor_parcela'      => 'float',
        'valor_entrada'      => 'float',
        'total_parcelamento' => 'float',
        'valor_restante'     => 'float',

        // Timestamps
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Valores padrão para atributos.
     */
/**
     * Valores padrão para atributos.
     */
    protected $attributes = [
        'status' => 'rascunho',
        'descontos' => 0,
        'valor_entrada' => 0,
        'total_parcelamento' => 0,
        'valor_restante' => 0,
        
        // CORREÇÃO: Mude de [] (array) para '[]' (string)
        'baloes_json' => '[]', 
    ];

    // ===================================================================
    // RELACIONAMENTOS
    // ===================================================================

    /**
     * Lead associado à proposta.
     */
    public function lead()
    {
        return $this->belongsTo(\App\Models\Lead::class, 'lead_id');
    }

    // ===================================================================
    // ACCESSORS & MUTATORS (REMOVIDOS)
    // ===================================================================
    // 
    // A formatação (ex: 'R$ 1.234,56') é uma responsabilidade da
    // camada de Apresentação (View/Screen), não do Modelo.
    // 
    // A PropostasListScreen já faz a formatação correta na
    // renderização da tabela. Manter essa lógica aqui 
    // (especialmente com 'abs()') pode causar conflitos.
    //

    // ===================================================================
    // SCOPES (FILTROS ÚTEIS)
    // ===================================================================

    /**
     * Scope: Propostas com diferença zero (equilibradas).
     * Ótimo para dashboards.
     */
    public function scopeEquilibradas($query)
    {
        // whereRaw é a forma correta de comparar floats
        return $query->whereRaw('ABS(valor_restante) < 0.01');
    }

    /**
     * Scope: Propostas com entrada maior que X.
     */
    public function scopeEntradaAcimaDe($query, $valor)
    {
        return $query->where('valor_entrada', '>=', $valor);
    }
}