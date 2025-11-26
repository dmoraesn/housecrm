<?php

namespace App\Enums;

enum LeadOrigem: string
{
    case SITE = 'site';
    case INSTAGRAM = 'instagram';
    case FACEBOOK = 'facebook';
    case INDICACAO = 'indicacao';
    case ANUNCIO = 'anuncio';
    case WHATSAPP = 'whatsapp';
    case GOOGLE = 'google';
    case EMAIL = 'email';
    case TELEFONE = 'telefone';
    case EVENTO = 'evento';
    case OUTRO = 'outro';

    public function label(): string
    {
        return match ($this) {
            self::SITE      => 'Site Oficial',
            self::INSTAGRAM => 'Instagram',
            self::FACEBOOK  => 'Facebook',
            self::INDICACAO => 'Indicação',
            self::ANUNCIO   => 'Anúncio Pago',
            self::WHATSAPP  => 'WhatsApp Direto',
            self::GOOGLE    => 'Google Search',
            self::EMAIL     => 'E-mail Marketing',
            self::TELEFONE  => 'Telefone (Cold Call)',
            self::EVENTO    => 'Evento / Feira',
            self::OUTRO     => 'Outra Origem',
        };
    }
}