<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'telefone',
        'origem',
        'status',
        'user_id',
    ];

    public const STATUS = [
        'novo',
        'qualificacao',
        'visita',
        'negociacao',
        'fechamento',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
