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
}