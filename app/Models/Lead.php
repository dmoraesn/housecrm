<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Screen\AsSource;

use OpenAI; // Import para o cliente OpenAI (pacote openai-php/client)

use AITemplate; // Import para o modelo de templates de IA
use App\Enums\LeadStatus; // Import do enum compartilhado

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

    protected $table = 'leads';

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'documento',
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
        'status' => LeadStatus::class,
        'origem' => LeadOrigem::class,
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

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

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

        $html .= '</div>';

        return $html;
    }

    public static function statusOptions(): array
    {
        return [
            LeadStatus::NOVO->value => LeadStatus::NOVO->label(),
            LeadStatus::QUALIFICACAO->value => LeadStatus::QUALIFICACAO->label(),
            LeadStatus::VISITA->value => LeadStatus::VISITA->label(),
            LeadStatus::NEGOCIACAO->value => LeadStatus::NEGOCIACAO->label(),
            LeadStatus::FECHAMENTO->value => LeadStatus::FECHAMENTO->label(),
            LeadStatus::PERDIDO->value => LeadStatus::PERDIDO->label(),
        ];
    }

    public static function origemOptions(): array
    {
        return array_column(LeadOrigem::cases(), 'value', 'value');
    }

    public function avancarEtapa(): bool
    {
        $index = array_search($this->status->value, self::FLUXO_VENDAS);

        if ($index === false || $index === count(self::FLUXO_VENDAS) - 1) {
            return false;
        }

        $this->status = LeadStatus::from(self::FLUXO_VENDAS[$index + 1]);

        return $this->save();
    }

    public function marcarComoPerdido(?string $motivo = null): bool
    {
        $this->status = LeadStatus::PERDIDO;

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
        $template = AITemplate::getTemplate($this->status->value);
        if (!$template) {
            return ['success' => false, 'message' => 'Status não suportado para IA.'];
        }

        $data = [
            'nome' => $this->nome ?? '',
            'email' => $this->email ?? '',
            'telefone' => $this->telefone_formatado ?? '',
            'origem' => $this->origem->value ?? '',
            'mensagem' => $this->mensagem ?? '',
            'valor_interesse' => $this->valor_interesse ?? '',
            'observacoes' => $this->observacoes ?? '',
        ];

        $prompt = AITemplate::getFormattedPrompt($this->status->value, $data);

        try {
            $client = OpenAI::client(env('OPENAI_API_KEY'));
            $response = $client->chat()->create([
                'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => $template['max_tokens'],
                'temperature' => 0.7,
            ]);

            $mensagemGerada = trim($response->choices[0]->message->content);

            $historicoAtual = $this->historico_interacoes ?? [];
            $historicoAtual[] = [
                'data' => now()->toDateTimeString(),
                'acao' => 'followup_ia',
                'mensagem' => $mensagemGerada,
                'resposta' => null,
            ];
            $this->historico_interacoes = $historicoAtual;
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
                $ultima['resposta'] = $resposta;
                $this->historico_interacoes[array_key_last($this->historico_interacoes)] = $ultima;
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
            $lead->status ??= LeadStatus::NOVO;
            $lead->order ??= self::nextOrderForStatus($lead->status);
            $lead->historico_interacoes ??= [];
        });

        static::updating(function (self $lead) {
            if ($lead->isDirty('status')) {
                $lead->order = self::nextOrderForStatus($lead->status);
            }
        });
    }

    private static function nextOrderForStatus(LeadStatus $status): int
    {
        return (int) self::where('status', $status->value)->max('order') + 1;
    }
}