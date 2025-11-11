<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Imovel extends Model
{
    use HasFactory, AsSource;

    protected $table = 'imoveis';

    protected $fillable = [
        'titulo', 'descricao', 'tipo', 'valor_venda', 'valor_aluguel',
        'quartos', 'banheiros', 'vagas', 'area', 'endereco',
        'cidade', 'estado', 'cep', 'status', 'construtora_id', 'corretor_id'
    ];

    public function construtora()
    {
        return $this->belongsTo(Construtora::class);
    }

    public function corretor()
    {
        return $this->belongsTo(User::class, 'corretor_id');
    }

    public function getStatusBadge()
    {
        return match ($this->status) {
            'disponivel' => '<span class="badge badge-success">Disponível</span>',
            'vendido'    => '<span class="badge badge-danger">Vendido</span>',
            'alugado'    => '<span class="badge badge-warning">Alugado</span>',
            'reservado'  => '<span class="badge badge-info">Reservado</span>',
            default      => '<span class="badge badge-secondary">—</span>',
        };
    }
}