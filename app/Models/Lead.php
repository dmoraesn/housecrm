<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Screen\AsSource;

class Lead extends Model
{
    use HasFactory, AsSource;

    // ===================================================================
    // CONFIGURAÇÃO
    // ===================================================================

    protected $table = 'leads';

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'origem',
        'mensagem',
        'status',
        'user_id',
        'data_contato',
        'valor_interesse',
        'observacoes',
        'order',
    ];

    protected $casts = [
        'data_contato'    => 'datetime',
        'valor_interesse' => 'decimal:2',
        'order'           => 'integer',
    ];

    /**
     * Os atributos "appended" que devem ser adicionados ao array do modelo.
     *
     * @var array
     */
    protected $appends = [
        'status_label',
        'status_badge',
        'telefone_formatado',
        'whatsapp_link',
    ];

    // ===================================================================
    // CONSTANTES
    // ===================================================================

    public const STATUS = [
        'novo'         => 'Novo Lead',
        'qualificacao' => 'Qualificação',
        'visita'       => 'Visita Marcada',
        'negociacao'   => 'Negociação',
        'fechamento'   => 'Fechamento',
        'perdido'      => 'Perdido',
    ];

    public const ORIGENS = [
        'Site', 'Instagram', 'Facebook', 'Indicação', 'Anúncio',
        'WhatsApp', 'Google', 'Email', 'Telefone', 'Evento', 'Outro'
    ];

    /**
     * Define o fluxo de status válidos para avanço.
     */
    public const FLUXO_VENDAS = ['novo', 'qualificacao', 'visita', 'negociacao', 'fechamento'];

    // ===================================================================
    // RELACIONAMENTOS
    // ===================================================================

    /**
     * O corretor (User) associado a este lead.
     */
    public function corretor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * As propostas associadas a este lead.
     */
    public function propostas(): HasMany
    {
        return $this->hasMany(Proposta::class, 'lead_id');
    }

    /**
     * Os contratos associados a este lead.
     */
    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class, 'lead_id');
    }

    /**
     * Os imóveis de interesse associados a este lead.
     */
    public function imoveisInteresse(): HasMany
    {
        return $this->hasMany(ImovelInteresse::class, 'lead_id');
    }

    // ===================================================================
    // ACCESSORS
    // ===================================================================

    /**
     * Retorna o rótulo legível do status atual.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? 'Indefinido';
    }

    /**
     * Retorna um badge HTML (Bootstrap) para o status atual.
     */
    public function getStatusBadgeAttribute(): string
    {
        $colors = [
            'novo'         => 'bg-info text-white',
            'qualificacao' => 'bg-primary',
            'visita'       => 'bg-warning text-dark',
            'negociacao'   => 'bg-orange text-white',
            'fechamento'   => 'bg-success',
            'perdido'      => 'bg-danger',
        ];

        $color = $colors[$this->status] ?? 'bg-secondary';
        return "<span class=\"badge {$color} fw-semibold\">{$this->status_label}</span>";
    }

    /**
     * Formata o número de telefone (detecta 10 ou 11 dígitos).
     */
    public function getTelefoneFormatadoAttribute(): ?string
    {
        if (!$this->telefone) return null;

        $digits = preg_replace('/\D/', '', $this->telefone);

        // Formata baseado no tamanho (11 = celular, 10 = fixo)
        return match (strlen($digits)) {
            11      => "({$digits[0]}{$digits[1]}) {$digits[2]}{$digits[3]}{$digits[4]}{$digits[5]}-{$digits[6]}{$digits[7]}{$digits[8]}{$digits[9]}{$digits[10]}",
            10      => "({$digits[0]}{$digits[1]}) {$digits[2]}{$digits[3]}{$digits[4]}-{$digits[5]}{$digits[6]}{$digits[7]}{$digits[8]}{$digits[9]}",
            default => $this->telefone,
        };
    }

    /**
     * Gera um link direto "clique para conversar" do WhatsApp.
     */
    public function getWhatsappLinkAttribute(): ?string
    {
        if (!$this->telefone) return null;
        $clean = preg_replace('/\D/', '', $this->telefone);
        
        // Adiciona o 55 (Brasil) se não estiver presente nos formatos comuns
        if (strlen($clean) <= 11) {
             $clean = "55{$clean}";
        }
       
        return "https://wa.me/{$clean}";
    }

    // ===================================================================
    // MÉTODOS ESTÁTICOS
    // ===================================================================

    /**
     * Retorna os status formatados para uso em Selects (Orchid).
     */
    public static function statusOptions(): array
    {
        return [
            'novo'         => '1 Novo Lead / Descoberta',
            'qualificacao' => '2 Qualificação / Entendimento',
            'visita'       => '3 Apresentação / Visita',
            'negociacao'   => '4 Proposta / Negociação',
            'fechamento'   => '5 Fechamento / Contrato',
            'perdido'      => '6 Perdido',
        ];
    }

    /**
     * Retorna as origens para uso em Selects (Orchid).
     */
    public static function origemOptions(): array
    {
        return array_combine(self::ORIGENS, self::ORIGENS);
    }

    // ===================================================================
    // MÉTODOS DE NEGÓCIO
    // ===================================================================

    /**
     * Avança o lead para a próxima etapa do funil de vendas.
     */
    public function avancarEtapa(): bool
    {
        $index = array_search($this->status, self::FLUXO_VENDAS);
        
        // Não avança se status não está no fluxo ou se já é o último
        if ($index === false || $index === count(self::FLUXO_VENDAS) - 1) {
            return false;
        }

        $this->status = self::FLUXO_VENDAS[$index + 1];
        
        // *** LINHA REMOVIDA DAQUI ***
        // $this->reordenarNoFluxo(); 
        // O evento 'booted::updating' cuidará da reordenação automaticamente.

        return $this->save();
    }

    /**
     * Marca o lead como perdido, opcionalmente adicionando um motivo.
     */
    public function marcarComoPerdido(string $motivo = null): bool
    {
        $this->status = 'perdido';
        if ($motivo) {
            $this->observacoes = "Perdido: {$motivo}\n\n" . ($this->observacoes ?? '');
        }
        return $this->save();
    }

    /**
     * Verifica se o lead já possui alguma proposta.
     */
    public function temProposta(): bool
    {
        return $this->propostas()->exists();
    }

    // ===================================================================
    // SCOPES
    // ===================================================================

    public function scopeNovo($query)
    {
        return $query->where('status', 'novo');
    }

    /**
     * Filtra leads que estão no fluxo de vendas ativo (não perdidos/ganhos).
     */
    public function scopeAtivo($query)
    {
        return $query->whereIn('status', self::FLUXO_VENDAS);
    }

    public function scopeSemCorretor($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeDoCorretor($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeHoje($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ===================================================================
    // EVENTOS (Observers do Model)
    // ===================================================================

    /**
     * O método "booted" do modelo.
     */
    protected static function booted(): void
    {
        /**
         * Define valores padrão ao criar um novo lead.
         */
        static::creating(function (self $lead) {
            // Garante que o status seja 'novo' se nenhum for fornecido
            $lead->status ??= 'novo';
            // Define a ordem inicial para o status
            $lead->order ??= self::nextOrderForStatus($lead->status);
        });

        /**
         * Reordena o lead se o status for alterado.
         */
        static::updating(function (self $lead) {
            // Verifica se o campo 'status' foi modificado
            if ($lead->isDirty('status')) {
                // Atribui a nova ordem baseada no *novo* status
                $lead->order = self::nextOrderForStatus($lead->status);
            }
        });
    }

    /**
     * Calcula a próxima posição de 'order' para um determinado status.
     * (Função de suporte para reordenação)
     */
    private static function nextOrderForStatus(string $status): int
    {
        // Pega a ordem máxima atual para esse status e soma 1
        return (int) self::where('status', $status)->max('order') + 1;
    }

    /**
     * Função privada de reordenação (Não mais usada por avancarEtapa).
     * Mantida aqui caso seja necessária em outro local.
     */
    private function reordenarNoFluxo(): void
    {
        $this->order = self::nextOrderForStatus($this->status);
    }
}