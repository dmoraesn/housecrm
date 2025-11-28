<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
// Importação do LeadStatus (Presumindo que será movido para App\Enums ou já exista)
use App\Enums\LeadStatus;

/**
 * Model for AI templates. Fallbacks provide contextual prompts; no final CTA.
 */
// REMOVIDO: A definição do enum LeadStatus: string foi removida daqui
// para evitar o erro "Class App\Models\LeadStatus not found"

class AITemplate extends Model
{
    use HasFactory;

    protected $table = 'ai_templates';

    protected $fillable = [
        'lead_status',
        'nome',
        'prompt',
        'max_tokens',
        'ativo',
        'created_by',
        'updated_by',
    ];

    /**
     * Internal fallback templates (used if no active DB record).
     */
    public const DEFAULT_TEMPLATES = [
        // CORREÇÃO: Usamos as strings dos valores do Enum (em vez do próprio objeto Enum)
        'novo' => [
            'prompt' => 'Você é um corretor proativo e cordial, com saudação leve (“Opa, {nome}!” ou similar).
                Crie uma mensagem curta (máx. 160 chars).
                Use {gancho_selecionado} como tema contextual, sem parecer título e sem repetir no final.
                Conecte naturalmente ao interesse inicial ({mensagem}/{origem}) com linguagem leve e direta.
                Finalize com um CTA simples, humano e natural.',
            'max_tokens' => 100,
        ],
        'qualificacao' => [
            'prompt' => 'Você é um corretor consultivo e direto. Use saudação informal moderada (“Tudo certo, {nome}?”).
                Crie uma mensagem curta (máx. 160 chars).
                Use {gancho_selecionado} como ponto de partida do assunto, sem repetir no final.
                Explique brevemente como a qualificação ajuda a obter melhores condições ({valor_interesse}).
                Mantenha linguagem clara e simples.
                Finalize com um CTA natural e suave.',
            'max_tokens' => 120,
        ],
        'visita' => [
            'prompt' => 'Você é um corretor objetivo, com leve informalidade (“Opa, {nome}!”).
                Crie uma mensagem curta (máx. 160 chars).
                Use {gancho_selecionado} como tema inicial de forma natural.
                Conecte ao fato de que o perfil do cliente já está definido.
                Não repita o gancho no final.
                Finalize sugerindo o agendamento da visita de forma direta e leve.',
            'max_tokens' => 100,
        ],
        'negociacao' => [
            'prompt' => 'Você é um corretor parceiro e direto. Use saudação informal moderada (“Oi, {nome}!”).
                Crie uma mensagem curta (máx. 160 chars).
                Use {gancho_selecionado} como tema inicial de forma natural, sem repetir no final.
                Conecte o tema à necessidade de alinhar valores/condições ({valor_interesse}) com clareza.
                Finalize com um CTA suave para avançar na proposta.',
            'max_tokens' => 120,
        ],
        'fechamento' => [
            'prompt' => 'Você é um corretor organizado e humano. Use saudação leve (“Tudo tranquilo, {nome}?”).
                Crie uma mensagem curta (máx. 160 chars).
                Use {gancho_selecionado} para contextualizar o assunto, sem repetir no final.
                Conecte o tema à organização e segurança da etapa final.
                Finalize com um CTA, convidativo e humano, sobre a formalização.',
            'max_tokens' => 100,
        ],
        'perdido' => [
            'prompt' => 'Você é um corretor que demonstra memória e proximidade. Use saudação informal moderada (“Opa, {nome}!”).
                Crie uma mensagem curta (máx. 160 chars).
                Use {gancho_selecionado} como tema da retomada, sem repetir no final.
                Conecte ao último interesse mencionado pelo cliente ({observacoes}).
                Finalize com um CTA leve para reavaliar opções, sem pressão.',
            'max_tokens' => 100,
        ],
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id() ?? 1;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id() ?? 1;
        });
    }

    /**
     * Retrieve template from DB or fallback.
     * @param string $status O status do lead para buscar o template.
     */
    public static function getTemplate(string $status): ?array
    {
        // CORREÇÃO: Reverteu a tipagem e a busca para aceitar a string.
        $record = self::where('lead_status', $status)
            ->where('ativo', true)
            ->first();

        if ($record) {
            return [
                'prompt' => $record->prompt,
                'max_tokens' => $record->max_tokens,
            ];
        }

        return self::DEFAULT_TEMPLATES[$status] ?? null;
    }

    /**
     * Replace placeholders in prompt with real data.
     * @param string $status O status do lead para buscar o template.
     */
    public static function getFormattedPrompt(string $status, array $data): string
    {
        $template = self::getTemplate($status);
        if (!$template || empty($template['prompt'])) {
            return '';
        }

        $replacements = [
            '{nome}' => $data['nome'] ?? '',
            '{email}' => $data['email'] ?? '',
            '{telefone}' => $data['telefone'] ?? '',
            '{origem}' => $data['origem'] ?? '',
            '{mensagem}' => $data['mensagem'] ?? '',
            '{valor_interesse}' => $data['valor_interesse'] ?? '',
            '{observacoes}' => $data['observacoes'] ?? '',
            '{gancho_selecionado}' => $data['gancho_selecionado'] ?? ($data['contexto_extra'] ?? ''),
        ];

        return strtr($template['prompt'], $replacements);
    }

    /**
     * Accessor for prompt preview in Orchid table.
     */
    public function getContentAttribute(): ?string
    {
        return $this->prompt ? Str::limit($this->prompt, 100) : null;
    }

    /**
     * Method for Orchid TD rendering.
     */
    public function getContent(): string
    {
        return Str::limit($this->prompt ?? '', 100);
    }
}