<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo responsável por armazenar e gerenciar o fluxo financeiro (Pro Soluto)
 * de um lead. Inclui cálculo de financiamento, percentuais e valores de entrada.
 */
class Fluxo extends Model
{
    use HasFactory;

    // ===================================================================
    // CONSTANTES DE STATUS (CORREÇÃO CRÍTICA)
    // ===================================================================
    public const STATUS_DRAFT = 'rascunho';
    public const STATUS_COMPLETED = 'concluido';
    public const STATUS_CANCELLED = 'cancelado';


    /**
     * Tabela vinculada ao modelo.
     *
     * @var string
     */
    protected $table = 'fluxos';

    /**
     * Campos atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lead_id',
        'construtora_id', // Adicionado: Faltava no $fillable, mas é usado no Screen
        'valor_imovel',
        'valor_avaliacao',
        'valor_financiado',
        'valor_bonus_descontos',
        'valor_assinatura_contrato',
        'valor_na_chaves',
        'entrada_minima',
        'parcelas_qtd',
        'valor_parcela',
        'total_parcelamento',
        'valor_total_entrada',
        'valor_restante',
        'financiamento_percentual',
        'base_calculo',
        'modo_calculo',
        'observacao',
        'status',
        'baloes', // Adicionado: Faltava no $fillable, mas é usado no Screen (Matrix)
    ];

    /**
     * Conversões automáticas de tipo.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'baloes' => 'array', // Corrigido para array
        'valor_imovel' => 'float',
        'valor_avaliacao' => 'float',
        'valor_financiado' => 'float',
        'valor_bonus_descontos' => 'float',
        'valor_assinatura_contrato' => 'float',
        'valor_na_chaves' => 'float',
        'entrada_minima' => 'float',
        'valor_parcela' => 'float',
        'total_parcelamento' => 'float',
        'valor_total_entrada' => 'float',
        'valor_restante' => 'float',
        'financiamento_percentual' => 'float',
    ];

    /**
     * Relacionamento: Fluxo pertence a um Lead.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lead()
    {
        return $this->belongsTo(\App\Models\Lead::class);
    }

    /**
     * Relacionamento: Fluxo pertence a uma Construtora.
     */
    public function construtora()
    {
        return $this->belongsTo(\App\Models\Construtora::class);
    }

    /**
     * Escopo auxiliar para fluxos ativos.
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    /**
     * Escopo auxiliar para fluxos concluídos.
     */
    public function scopeConcluidos($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
// 93 linhas mantidas