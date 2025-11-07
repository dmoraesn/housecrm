<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comissao extends Model
{
    protected $fillable = [
        'corretor',
        'imovel',
        'valor',
        'percentual',
        'status',
        'data_pagamento',
    ];

    protected $casts = [
        'data_pagamento' => 'datetime',
    ];
}
