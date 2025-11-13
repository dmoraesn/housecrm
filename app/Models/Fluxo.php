<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo responsável por armazenar e gerenciar o fluxo financeiro (Pro Soluto)
 * de um lead. Inclui cálculo de financiamento, percentuais e valores de entrada.
 */
class Fluxo extends Model
{
    use HasFactory;

    /**
     * Constantes para statuses do fluxo.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELADO = 'cancelado';

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
        'baloes', // Adicionado para Matrix de balões
        'observacao',
        'status',
    ];

    /**
     * Conversões automáticas de tipo.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'valor_imovel' => 'decimal:2',
        'valor_avaliacao' => 'decimal:2',
        'valor_financiado' => 'decimal:2',
        'valor_bonus_descontos' => 'decimal:2',
        'valor_assinatura_contrato' => 'decimal:2',
        'valor_na_chaves' => 'decimal:2',
        'entrada_minima' => 'decimal:2',
        'valor_parcela' => 'decimal:2',
        'total_parcelamento' => 'decimal:2',
        'valor_total_entrada' => 'decimal:2',
        'valor_restante' => 'decimal:2',
        'financiamento_percentual' => 'decimal:0', // Percentual inteiro ou com casas?
        'baloes' => 'array', // Para serialização JSON automática
    ];

    /**
     * Relacionamento: Fluxo pertence a um Lead.
     *
     * @return BelongsTo
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Escopo auxiliar para fluxos ativos (exclui cancelados).
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELADO);
    }

    /**
     * Escopo auxiliar para fluxos concluídos.
     */
    public function scopeConcluidos($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Escopo auxiliar para fluxos em rascunho (novo, para consistência).
     */
    public function scopeRascunhos($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    // Acessors para formatação BRL (exemplo para campos principais; expanda se necessário)

    /**
     * Acessor: Valor do imóvel formatado em BRL.
     */
    public function getValorImovelFormattedAttribute(): string
    {
        return $this->formatBrl($this->valor_imovel);
    }

    /**
     * Acessor: Valor de avaliação formatado em BRL.
     */
    public function getValorAvaliacaoFormattedAttribute(): string
    {
        return $this->formatBrl($this->valor_avaliacao);
    }

    /**
     * Acessor: Entrada mínima formatada em BRL.
     */
    public function getEntradaMinimaFormattedAttribute(): string
    {
        return $this->formatBrl($this->entrada_minima);
    }

    // Adicione mais acessors semelhantes para outros campos monetários...

    /**
     * Função auxiliar para formatar valor em BRL.
     *
     * @param float|null $value
     * @return string
     */
    protected function formatBrl($value): string
    {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }
}