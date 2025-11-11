<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Comissao extends Model
{
    use HasFactory, AsSource;

    protected $table = 'comissoes'; // ← CORREÇÃO AQUI

    protected $fillable = [
        'lead_id', 'user_id', 'valor', 'percentual', 'status', 'data_pagamento', 'observacoes'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function corretor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getStatusBadge()
    {
        return match ($this->status) {
            'pago'      => '<span class="badge badge-success">Pago</span>',
            'pendente'  => '<span class="badge badge-warning">Pendente</span>',
            'cancelado' => '<span class="badge badge-danger">Cancelado</span>',
            default     => '<span class="badge badge-secondary">—</span>',
        };
    }
}