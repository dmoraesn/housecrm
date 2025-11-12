<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fluxo extends Model
{
    use HasFactory;

    protected $table = 'fluxos';

    protected $fillable = [
        'cliente_id',
        'valor_imovel',
        'valor_avaliacao',
        'valor_entrada',
        'valor_bonus_descontos',
        'valor_assinatura_contrato',
        'valor_na_chaves',
        'baloes',
        'parcelas_qtd',
        'valor_parcela',
        'total_parcelamento',
        'valor_total_entrada',
        'valor_restante',
        'observacao',
        'status',
        'created_by',
    ];

    protected $casts = [
        'baloes' => 'array',
        'valor_imovel' => 'decimal:2',
        'valor_avaliacao' => 'decimal:2',
        'valor_entrada' => 'decimal:2',
        'valor_bonus_descontos' => 'decimal:2',
        'valor_assinatura_contrato' => 'decimal:2',
        'valor_na_chaves' => 'decimal:2',
        'valor_parcela' => 'decimal:2',
        'total_parcelamento' => 'decimal:2',
        'valor_total_entrada' => 'decimal:2',
        'valor_restante' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
