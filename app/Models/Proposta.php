<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposta extends Model
{
    protected $fillable = [
        'cliente',
        'imovel',
        'valor',
        'status',
        'data_envio',
    ];

    protected $casts = [
        'data_envio' => 'datetime',
    ];
}
