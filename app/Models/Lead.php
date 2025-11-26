<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadOrigem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Screen\AsSource;
use OpenAI;
use AITemplate;
use App\Enums\LeadStatus;

class Lead extends Model
{
    use HasFactory, AsSource;

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
        'historico_interacoes',
    ];

    protected $casts = [
        'data_contato' => 'datetime',
        'valor_interesse' => 'decimal:2',
        'order' => 'integer',
        'status' => \App\Enums\LeadStatus::class,
        'origem' => \App\Enums\LeadOrigem::class,
        'historico_interacoes' => 'array',
    ];

    protected $appends = [
        'status_label',
        'status_badge',
        'telefone_formatado',
        'whatsapp_link',
        'historico_formatado',
    ];

    public const FLUXO_VENDAS = [
        LeadStatus::NOVO->value,
        LeadStatus::QUALIFICACAO->value,
        LeadStatus::VISITA->value,
        LeadStatus::NEGOCIACAO->value,
        LeadStatus::FECHAMENTO->value,
    ];

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

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS CORREÇÃO ORCHID/ENUM
    |--------------------------------------------------------------------------
    */

    /**
     * Garante que o Orchid receba o valor string do Enum, e não o objeto.
     */
    public function getStatusAttribute($value): string|LeadStatus|null
    {
        if ($value instanceof LeadStatus) {
            return $value->value;
        }

        $status = $this->attributes['status'];

        if ($status === null) {
            return null; // Retorna null se o valor bruto for null
        }

        // Se for um Enum e o valor for string, retorna o valor string
        if ($this->hasCast('status', LeadStatus::class)) {
            // CORREÇÃO DE SEGURANÇA: tryFrom pode receber string|int, mas não null.
            // A verificação de $status === null já cobre isso.
            $enumInstance = LeadStatus::tryFrom($status); 
            return $enumInstance?->value ?? $status;
        }
        
        return $status;
    }
    
    public function getOrigemAttribute($value): string|LeadOrigem|null
    {
        if ($value instanceof LeadOrigem) {
            return $value->value;
        }
        
        $origem = $this->attributes['origem'];
        
        // CORREÇÃO PRINCIPAL: Se o valor bruto for null, retorna null.
        if ($origem === null) {
            return null;
        }

        if ($this->hasCast('origem', LeadOrigem::class)) {
            // Agora $origem é garantido como string (ou o valor do Enum)
            $enumInstance = LeadOrigem::tryFrom($origem);
            return $enumInstance?->value ?? $origem;
        }

        return $origem;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS COMPATÍVEIS COM STRING E ENUM (EXISTENTES)
    |--------------------------------------------------------------------------
    */
    
    public function getStatusLabelAttribute(): string
    {
        $statusValue = $this->attributes['status'];
        
        if ($statusValue === null) {
            return '';
        }

        $enum = LeadStatus::tryFrom($statusValue);

        return $enum?->label() ?? '';
    }

    public function getStatusBadgeAttribute(): string
    {
        $statusValue = $this->attributes['status'];
        
        if (!$statusValue) {
            return '<span class="badge bg-secondary text-white fw-semibold">Novo</span>';
        }

        $colors = [
            LeadStatus::NOVO->value => 'bg-info text-white',
            LeadStatus::QUALIFICACAO->value => 'bg-primary text-white',
            LeadStatus::VISITA->value => 'bg-warning text-dark',
            LeadStatus::NEGOCIACAO->value => 'bg-warning text-dark',
            LeadStatus::FECHAMENTO->value => 'bg-success text-white',
            LeadStatus::PERDIDO->value => 'bg-danger text-white',
        ];

        $label = $this->status_label;
        $color = $colors[$statusValue] ?? 'bg-secondary text-white';

        return "<span class=\"badge {$color} fw-semibold\">{$label}</span>";
    }

    public function getTelefoneFormatadoAttribute(): ?string
    {
        if (!$this->telefone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->telefone);

        return match (strlen($digits)) {
            11 => "({$digits[0]}{$digits[1]}) {$digits[2]}{$digits[3]}{$digits[4]}{$digits[5]}{$digits[6]}-{$digits[7]}{$digits[8]}{$digits[9]}{$digits[10]}",
            10 => "({$digits[0]}{$digits[1]}) {$digits[2]}{$digits[3]}{$digits[4]}{$digits[5]}-{$digits[6]}{$digits[7]}{$digits[8]}{$digits[9]}",
            default => $this->telefone,
        };
    }
    
    public function getWhatsappLinkAttribute(): ?string
    {
        if (!$this->telefone) {
            return null;
        }

        $clean = preg_replace('/\D/', '', $this->telefone);
        $prefix = strlen($clean) <= 11 ? '55' : '';

        return "https://wa.me/{$prefix}{$clean}";
    }

    public function getHistoricoFormatadoAttribute(): string
    {
        if (empty($this->historico_interacoes)) {
            return '<p class="text-muted">Nenhuma interação registrada.</p>';
        }

        $html = '<div class="timeline">';

        foreach (array_reverse($this->historico_interacoes) as $interacao) {
            $data = $interacao['data'] ?? now()->toDateTimeString();
            $acao = $interacao['acao'] ?? 'Ação';
            $mensagem = $interacao['mensagem'] ?? '';
            $resposta = $interacao['resposta'] ?? null;

            $html .= "
                <div class=\"timeline-item\">
                    <small>{$data}</small><br>
                    <strong>{$acao}:</strong> {$mensagem}
                    " . ($resposta ? "<br><em>Resposta: {$resposta}</em>" : '') . "
                </div>
            ";
        }

        return $html . '</div>';
    }

    public static function statusOptions(): array
    {
        return collect(LeadStatus::cases())
            ->filter(fn (LeadStatus $status) => in_array($status->value, self::FLUXO_VENDAS, true))
            ->mapWithKeys(fn (LeadStatus $status) => [$status->value => $status->label()])
            ->toArray();
    }

    public static function origemOptions(): array
    {
        return collect(LeadOrigem::cases())
            ->mapWithKeys(fn (LeadOrigem $origem) => [$origem->value => $origem->label()])
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | LÓGICA DE AVANÇAR E PERDER
    |--------------------------------------------------------------------------
    */

    public function avancarEtapa(): bool
    {
        $statusValue = $this->status instanceof LeadStatus ? $this->status->value : $this->status;

        $index = array_search($statusValue, self::FLUXO_VENDAS);

        if ($index === false || $index === count(self::FLUXO_VENDAS) - 1) {
            return false;
        }

        // CORREÇÃO: atribui STRING sempre
        $this->status = self::FLUXO_VENDAS[$index + 1];

        return $this->save();
    }

    public function marcarComoPerdido(?string $motivo = null): bool
    {
        // CORREÇÃO: atribuir string, não Enum
        $this->status = LeadStatus::PERDIDO->value;

        if ($motivo) {
            $this->observacoes = "Perdido: {$motivo}\n\n" . ($this->observacoes ?? '');
        }

        return $this->save();
    }

    public function temProposta(): bool
    {
        return $this->propostas()->exists();
    }

    public function gerarFollowUpIA(): array
    {
        $statusValue = $this->status instanceof LeadStatus ? $this->status->value : $this->status;

        if (!$statusValue) {
            return ['success' => false, 'message' => 'Status não definido para IA.'];
        }

        $template = AITemplate::getTemplate($statusValue);

        if (!$template) {
            return ['success' => false, 'message' => 'Status não suportado para IA.'];
        }

        $data = [
            'nome' => $this->nome ?? '',
            'email' => $this->email ?? '',
            'telefone' => $this->telefone_formatado ?? '',
            // No Orchid/Laravel, quando o atributo é acessado, ele chama o accessor que retorna a string,
            // garantindo que 'origem' seja string ou null.
            'origem' => $this->origem ?? $this->attributes['origem'] ?? '', 
            'mensagem' => $this->mensagem ?? '',
            'valor_interesse' => $this->valor_interesse ?? '',
            'observacoes' => $this->observacoes ?? '',
        ];

        $prompt = AITemplate::getFormattedPrompt($statusValue, $data);

        try {
            $client = OpenAI::client(env('OPENAI_API_KEY'));

            $response = $client->chat()->create([
                'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => $template['max_tokens'],
                'temperature' => 0.7,
            ]);

            $mensagemGerada = trim($response->choices[0]->message->content);

            $historico = $this->historico_interacoes ?? [];
            $historico[] = [
                'data' => now()->toDateTimeString(),
                'acao' => 'followup_ia',
                'mensagem' => $mensagemGerada,
                'resposta' => null,
            ];

            $this->historico_interacoes = $historico;
            $this->save();

            return ['success' => true, 'message' => $mensagemGerada];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erro na API: ' . $e->getMessage()];
        }
    }

    public function adicionarRespostaHistorico(string $resposta): void
    {
        if (!empty($this->historico_interacoes)) {
            $ultima = end($this->historico_interacoes);

            if ($ultima['acao'] === 'followup_ia' && is_null($ultima['resposta'])) {
                $key = array_key_last($this->historico_interacoes);
                $this->historico_interacoes[$key]['resposta'] = $resposta;
                $this->save();
            }
        }
    }

    public function scopeNovo(Builder $query): Builder
    {
        return $query->where('status', LeadStatus::NOVO->value);
    }

    public function scopeAtivo(Builder $query): Builder
    {
        return $query->whereIn('status', self::FLUXO_VENDAS);
    }

    public function scopeSemCorretor(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    public function scopeDoCorretor(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeHoje(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    protected static function booted(): void
    {
        static::creating(function (self $lead) {
            // Garante que o valor salvo seja string
            $lead->status ??= LeadStatus::NOVO->value;
            $lead->order ??= self::nextOrderForStatus($lead->status);
            $lead->historico_interacoes ??= [];
        });

        static::updating(function (self $lead) {
            if ($lead->isDirty('status')) {
                 // Garante que o valor salvo seja string
                $lead->order = self::nextOrderForStatus($lead->status);
            }
        });
    }

    private static function nextOrderForStatus(LeadStatus|string $status): int
    {
        $value = $status instanceof LeadStatus ? $status->value : $status;

        return (int) self::where('status', $value)->max('order') + 1;
    }
}