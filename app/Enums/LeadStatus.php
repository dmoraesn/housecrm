<?php

namespace App\Enums;

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