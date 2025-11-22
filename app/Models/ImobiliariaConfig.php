<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Attachment\Models\Attachment;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class ImobiliariaConfig extends Model
{
    use AsSource, Filterable, Attachable;

    protected $fillable = [
        'logo_id', // <--- Adicionado
        'nome_fantasia',
        'razao_social',
        'cnpj',
        'creci',
        'telefone',
        'email',
        'endereco_completo',
    ];

    /**
     * Relacionamento correto para o Logo
     */
    public function logo()
    {
        return $this->belongsTo(Attachment::class, 'logo_id');
    }
}