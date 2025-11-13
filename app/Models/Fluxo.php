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
    ];

    /**
     * Conversões automáticas de tipo.
     *
     * @var array<string, string>
     */
    protected $casts = [
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
        return $this->belongsTo(Lead::class);
    }

    /**
     * Escopo auxiliar para fluxos ativos.
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', '!=', 'cancelado');
    }

    /**
     * Escopo auxiliar para fluxos concluídos.
     */
    public function scopeConcluidos($query)
    {
        return $query->where('status', 'completed');
    }
}
