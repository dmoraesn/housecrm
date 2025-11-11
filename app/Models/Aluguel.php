<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Aluguel extends Model
{
    use HasFactory, AsSource;

    protected $table = 'alugueis';

    protected $fillable = [
        'imovel_id', 'inquilino_id', 'corretor_id', 'valor_mensal',
        'valor_caucao', 'data_inicio', 'data_fim', 'status', 'observacoes'
    ];

    public function imovel()
    {
        return $this->belongsTo(Imovel::class);
    }

    public function inquilino()
    {
        return $this->belongsTo(User::class, 'inquilino_id');
    }

    public function corretor()
    {
        return $this->belongsTo(User::class, 'corretor_id');
    }

    public function getStatusBadge()
    {
        return match ($this->status) {
            'ativo'     => '<span class="badge badge-success">Ativo</span>',
            'encerrado' => '<span class="badge badge-secondary">Encerrado</span>',
            'cancelado' => '<span class="badge badge-danger">Cancelado</span>',
            default     => '<span class="badge badge-warning">—</span>',
        };
    }
}