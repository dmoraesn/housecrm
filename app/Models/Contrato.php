<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Contrato extends Model
{
    use HasFactory, AsSource;

    protected $table = 'contratos';

    protected $fillable = [
        'lead_id', 'imovel_id', 'comprador_id', 'corretor_id', 'tipo',
        'valor_total', 'valor_entrada', 'parcelas', 'data_assinatura',
        'data_vencimento', 'status', 'observacoes'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function imovel()
    {
        return $this->belongsTo(Imovel::class);
    }

    public function comprador()
    {
        return $this->belongsTo(User::class, 'comprador_id');
    }

    public function corretor()
    {
        return $this->belongsTo(User::class, 'corretor_id');
    }

    public function getStatusBadge()
    {
        return match ($this->status) {
            'ativo'      => '<span class="badge badge-success">Ativo</span>',
            'finalizado' => '<span class="badge badge-secondary">Finalizado</span>',
            'cancelado'  => '<span class="badge badge-danger">Cancelado</span>',
            default      => '<span class="badge badge-warning">—</span>',
        };
    }
}