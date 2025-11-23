<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Classe responsável por armazenar e formatar os prompts de IA.
 * Nova regra: gancho contextualiza, NÃO é CTA final.
 */
enum LeadStatus: string
{
    case NOVO = 'novo';
    case QUALIFICACAO = 'qualificacao';
    case VISITA = 'visita';
    case NEGOCIACAO = 'negociacao';
    case FECHAMENTO = 'fechamento';
    case PERDIDO = 'perdido';
}
class AITemplate extends Model
{
    public const TEMPLATES_IA = [
        /*
        |--------------------------------------------------------------------------
        | 1. NOVO — Gancho como tema, CTA natural ao final
        |--------------------------------------------------------------------------
        */
        LeadStatus::NOVO->value => [
            'prompt' =>
                'Você é um corretor imobiliário proativo e cordial. Use informalidade leve ("Opa, {nome}!"). 
                Crie uma mensagem curta (máx. 160 chars) usando o gancho "{gancho_selecionado}" como tema do assunto.
                O gancho NÃO deve aparecer como frase final. 
                Conecte o assunto ao interesse inicial ({mensagem}/{origem}) e finalize com um CTA simples e humano.',
            'max_tokens' => 100,
        ],

        /*
        |--------------------------------------------------------------------------
        | 2. QUALIFICAÇÃO — Gancho contextual, CTA suave
        |--------------------------------------------------------------------------
        */
        LeadStatus::QUALIFICACAO->value => [
            'prompt' =>
                'Você é um corretor consultivo e direto. Use informalidade moderada ("Tudo certo, {nome}?").
                Crie uma mensagem curta (máx. 160 chars) usando "{gancho_selecionado}" como tema que puxa o assunto.
                Explique brevemente como a qualificação ajuda a obter melhores condições ({valor_interesse}) 
                e finalize com um CTA natural, sem repetir o gancho.',
            'max_tokens' => 120,
        ],

        /*
        |--------------------------------------------------------------------------
        | 3. VISITA — Gancho contextual, foco em agendamento
        |--------------------------------------------------------------------------
        */
        LeadStatus::VISITA->value => [
            'prompt' =>
                'Você é um corretor objetivo. Pode usar informalidade leve ("Opa, {nome}!").
                Crie uma mensagem curta (máx. 160 chars) usando "{gancho_selecionado}" como tema inicial.
                Conecte o assunto ao fato de que o perfil do cliente já está claro
                e finalize sugerindo o agendamento da visita, sem usar o gancho na frase final.',
            'max_tokens' => 100,
        ],

        /*
        |--------------------------------------------------------------------------
        | 4. NEGOCIAÇÃO — Gancho como tema, CTA de definição de proposta
        |--------------------------------------------------------------------------
        */
        LeadStatus::NEGOCIACAO->value => [
            'prompt' =>
                'Você é um corretor parceiro e direto. Use informalidade moderada ("Oi {nome}!").
                Crie uma mensagem curta (máx. 160 chars) usando o gancho "{gancho_selecionado}" como tema inicial.
                Conecte esse tema à necessidade de alinhar valores/condições ({valor_interesse}) 
                e finalize com um CTA suave para avançar na proposta.',
            'max_tokens' => 120,
        ],

        /*
        |--------------------------------------------------------------------------
        | 5. FECHAMENTO — Gancho contextual, CTA sobre documentos/assinatura
        |--------------------------------------------------------------------------
        */
        LeadStatus::FECHAMENTO->value => [
            'prompt' =>
                'Você é um corretor organizado e humano. Use informalidade leve ("Tudo tranquilo, {nome}?").
                Crie uma mensagem curta (máx. 160 chars) usando o gancho "{gancho_selecionado}" para contextualizar o assunto.
                Conecte o tema à organização e segurança da etapa final e finalize com um CTA objetivo
                sem repetir o gancho.',
            'max_tokens' => 100,
        ],

        /*
        |--------------------------------------------------------------------------
        | 6. PERDIDO — Gancho como assunto da retomada, CTA leve
        |--------------------------------------------------------------------------
        */
        LeadStatus::PERDIDO->value => [
            'prompt' =>
                'Você é um corretor que demonstra memória e proximidade. Use informalidade moderada ("Opa, {nome}!").
                Crie uma mensagem curta (máx. 160 chars) usando o gancho "{gancho_selecionado}" como tema da retomada.
                Conecte o assunto ao último interesse do cliente ({observacoes})
                e finalize com um CTA leve, convidando para reavaliar sem pressão.',
            'max_tokens' => 100,
        ],
    ];

    /**
     * Retorna o template bruto do status.
     */
    public static function getTemplate(string $status): ?array
    {
        return self::TEMPLATES_IA[$status] ?? null;
    }

    /**
     * Formata o prompt substituindo placeholders.
     * Usa casting defensivo para evitar crashes com null/objetos.
     */
    public static function getFormattedPrompt(string $status, array $data): string
    {
        $template = self::getTemplate($status);
        if (!$template) {
            return '';
        }

        $placeholders = [
            '{nome}',
            '{email}',
            '{telefone}',
            '{origem}',
            '{mensagem}',
            '{valor_interesse}',
            '{observacoes}',
            '{gancho_selecionado}',
        ];

        $values = [
            (string)($data['nome'] ?? ''),
            (string)($data['email'] ?? ''),
            (string)($data['telefone'] ?? ''),
            (string)($data['origem'] ?? ''),
            (string)($data['mensagem'] ?? ''),
            (string)($data['valor_interesse'] ?? ''),
            (string)($data['observacoes'] ?? ''),
            (string)($data['gancho_selecionado'] ?? ($data['contexto_extra'] ?? '')),
        ];

        return str_replace($placeholders, $values, $template['prompt']);
    }
}