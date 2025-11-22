<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Screen\AsSource;

enum LeadStatus: string
{
    case NOVO = 'novo';
    case QUALIFICACAO = 'qualificacao';
    case VISITA = 'visita';
    case NEGOCIACAO = 'negociacao';
    case FECHAMENTO = 'fechamento';
    case PERDIDO = 'perdido';

    public function label(): string
    {
        return match ($this) {
            self::NOVO => 'Novo Lead',
            self::QUALIFICACAO => 'Qualificação',
            self::VISITA => 'Visita Marcada',
            self::NEGOCIACAO => 'Negociação',
            self::FECHAMENTO => 'Fechamento',
            self::PERDIDO => 'Perdido',
        };
    }
}

enum LeadOrigem: string
{
    case SITE = 'Site';
    case INSTAGRAM = 'Instagram';
    case FACEBOOK = 'Facebook';
    case INDICACAO = 'Indicação';
    case ANUNCIO = 'Anúncio';
    case WHATSAPP = 'WhatsApp';
    case GOOGLE = 'Google';
    case EMAIL = 'Email';
    case TELEFONE = 'Telefone';
    case EVENTO = 'Evento';
    case OUTRO = 'Outro';
}

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
        'documento', // Adicionado para suportar a view (verificar se existe na tabela)
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
        'data_contato' => 'datetime',
        'valor_interesse' => 'decimal:2',
        'order' => 'integer',
        'status' => LeadStatus::class,
        'origem' => LeadOrigem::class,
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

    public const FLUXO_VENDAS = [
        LeadStatus::NOVO->value,
        LeadStatus::QUALIFICACAO->value,
        LeadStatus::VISITA->value,
        LeadStatus::NEGOCIACAO->value,
        LeadStatus::FECHAMENTO->value,
    ];

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
        return $this->status->label();
    }

    /**
     * Retorna um badge HTML (Bootstrap) para o status atual.
     */
    public function getStatusBadgeAttribute(): string
    {
        $colors = [
            LeadStatus::NOVO->value => 'bg-info text-white',
            LeadStatus::QUALIFICACAO->value => 'bg-primary text-white',
            LeadStatus::VISITA->value => 'bg-warning text-dark',
            LeadStatus::NEGOCIACAO->value => 'bg-warning text-dark',
            LeadStatus::FECHAMENTO->value => 'bg-success text-white',
            LeadStatus::PERDIDO->value => 'bg-danger text-white',
        ];

        $color = $colors[$this->status->value] ?? 'bg-secondary text-white';
        return "<span class=\"badge {$color} fw-semibold\">{$this->status_label}</span>";
    }

    /**
     * Formata o número de telefone (detecta 10 ou 11 dígitos).
     */
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

    /**
     * Gera um link direto "clique para conversar" do WhatsApp.
     */
    public function getWhatsappLinkAttribute(): ?string
    {
        if (!$this->telefone) {
            return null;
        }

        $clean = preg_replace('/\D/', '', $this->telefone);
        $prefix = strlen($clean) <= 11 ? '55' : '';

        return "https://wa.me/{$prefix}{$clean}";
    }

    // ===================================================================
    // MÉTODOS ESTÁTICOS
    // ===================================================================

    /**
     * Retorna os status formatados para uso em Selects (Orchid).
     *
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            LeadStatus::NOVO->value => '1 Novo Lead / Descoberta',
            LeadStatus::QUALIFICACAO->value => '2 Qualificação / Entendimento',
            LeadStatus::VISITA->value => '3 Apresentação / Visita',
            LeadStatus::NEGOCIACAO->value => '4 Proposta / Negociação',
            LeadStatus::FECHAMENTO->value => '5 Fechamento / Contrato',
            LeadStatus::PERDIDO->value => '6 Perdido',
        ];
    }

    /**
     * Retorna as origens para uso em Selects (Orchid).
     *
     * @return array<string, string>
     */
    public static function origemOptions(): array
    {
        return array_column(LeadOrigem::cases(), 'value', 'value');
    }

    // ===================================================================
    // MÉTODOS DE NEGÓCIO
    // ===================================================================

    /**
     * Avança o lead para a próxima etapa do funil de vendas.
     */
    public function avancarEtapa(): bool
    {
        $index = array_search($this->status->value, self::FLUXO_VENDAS);

        if ($index === false || $index === count(self::FLUXO_VENDAS) - 1) {
            return false;
        }

        $this->status = LeadStatus::from(self::FLUXO_VENDAS[$index + 1]);
        return $this->save();
    }

    /**
     * Marca o lead como perdido, opcionalmente adicionando um motivo.
     */
    public function marcarComoPerdido(?string $motivo = null): bool
    {
        $this->status = LeadStatus::PERDIDO;

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

    /**
     * Filtra leads que estão no status 'novo'.
     */
    public function scopeNovo(Builder $query): Builder
    {
        return $query->where('status', LeadStatus::NOVO);
    }

    /**
     * Filtra leads que estão no fluxo de vendas ativo (não perdidos/ganhos).
     */
    public function scopeAtivo(Builder $query): Builder
    {
        return $query->whereIn('status', self::FLUXO_VENDAS);
    }

    /**
     * Filtra leads que ainda não foram atribuídos a um corretor.
     */
    public function scopeSemCorretor(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    /**
     * Filtra leads atribuídos a um corretor específico.
     */
    public function scopeDoCorretor(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtra leads criados na data de hoje.
     */
    public function scopeHoje(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    // ===================================================================
    // EVENTOS
    // ===================================================================

    /**
     * Define valores padrão e reordena ao criar ou atualizar um lead.
     */
    protected static function booted(): void
    {
        static::creating(function (self $lead) {
            $lead->status ??= LeadStatus::NOVO;
            $lead->order ??= self::nextOrderForStatus($lead->status);
        });

        static::updating(function (self $lead) {
            if ($lead->isDirty('status')) {
                $lead->order = self::nextOrderForStatus($lead->status);
            }
        });
    }

    /**
     * Calcula a próxima posição de 'order' para um determinado status.
     */
    private static function nextOrderForStatus(LeadStatus $status): int
    {
        return (int) self::where('status', $status)->max('order') + 1;
    }
}