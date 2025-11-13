<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Screen\AsSource;

/**
 * Modelo Lead – representa um lead de vendas no funil.
 *
 * Possui:
 *  • Configuração de tabela, fillable e casts
 *  • Constantes de status e origens
 *  • Relacionamentos (corretor, propostas, contratos, imóveis de interesse)
 *  • Accessors (rótulo, badge, telefone formatado, link WhatsApp)
 *  • Métodos estáticos para selects
 *  • Métodos de negócio (avançar etapa, marcar perdido, tem proposta)
 *  • Scopes (novo, ativos, sem corretor, do corretor, hoje)
 *  • Eventos booted (ordenação automática)
 */
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
        'WhatsApp', 'Google', 'Email', 'Telefone', 'Evento', 'Outro',
    ];

    public const FLUXO_VENDAS = ['novo', 'qualificacao', 'visita', 'negociacao', 'fechamento'];

    // ===================================================================
    // RELACIONAMENTOS
    // ===================================================================
    public function corretor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function propostas(): HasMany
    {
        return $this->hasMany(Proposta::class, 'lead_id');
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class, 'lead_id');
    }

    public function imoveisInteresse(): HasMany
    {
        return $this->hasMany(ImovelInteresse::class, 'lead_id');
    }

    // ===================================================================
    // ACCESSORS
    // ===================================================================
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? 'Indefinido';
    }

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

    public function getTelefoneFormatadoAttribute(): ?string
    {
        if (!$this->telefone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->telefone);

        return match (strlen($digits)) {
            11 => "({$digits[0]}{$digits[1]}) {$digits[2]}{$digits[3]}{$digits[4]}{$digits[5]}-{$digits[6]}{$digits[7]}{$digits[8]}{$digits[9]}{$digits[10]}",
            10 => "({$digits[0]}{$digits[1]}) {$digits[2]}{$digits[3]}{$digits[4]}-{$digits[5]}{$digits[6]}{$digits[7]}{$digits[8]}{$digits[9]}",
            default => $this->telefone,
        };
    }

    public function getWhatsappLinkAttribute(): ?string
    {
        if (!$this->telefone) {
            return null;
        }

        $clean = preg_replace('/\D/', '', $this->telefone);

        if (strlen($clean) <= 11) {
            $clean = "55{$clean}";
        }

        return "https://wa.me/{$clean}";
    }

    // ===================================================================
    // MÉTODOS ESTÁTICOS
    // ===================================================================
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

    public static function origemOptions(): array
    {
        return array_combine(self::ORIGENS, self::ORIGENS);
    }

    // ===================================================================
    // MÉTODOS DE NEGÓCIO
    // ===================================================================
    public function avancarEtapa(): bool
    {
        $index = array_search($this->status, self::FLUXO_VENDAS);

        if ($index === false || $index === count(self::FLUXO_VENDAS) - 1) {
            return false;
        }

        $this->status = self::FLUXO_VENDAS[$index + 1];

        return $this->save();
    }

    public function marcarComoPerdido(string $motivo = null): bool
    {
        $this->status = 'perdido';

        if ($motivo) {
            $this->observacoes = "Perdido: {$motivo}\n\n" . ($this->observacoes ?? '');
        }

        return $this->save();
    }

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

    public function scopeAtivos($query)
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
    protected static function booted(): void
    {
        static::creating(function (self $lead) {
            $lead->status ??= 'novo';
            $lead->order  ??= self::nextOrderForStatus($lead->status);
        });

        static::updating(function (self $lead) {
            if ($lead->isDirty('status')) {
                $lead->order = self::nextOrderForStatus($lead->status);
            }
        });
    }

    private static function nextOrderForStatus(string $status): int
    {
        return (int) self::where('status', $status)->max('order') + 1;
    }

    private function reordenarNoFluxo(): void
    {
        $this->order = self::nextOrderForStatus($this->status);
    }
}