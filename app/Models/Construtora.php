<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;

class Construtora extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $table = 'construtoras';

    protected $fillable = [
        'nome',
        'nome_fantasia',
        'cnpj',
        'telefone',
        'email',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'socios',
        'situacao',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $attributes = [
        'status' => true,
    ];

    // Permite busca e filtro na listagem Orchid
    protected $allowedFilters = [
        'nome'      => Like::class,
        'cnpj'      => Like::class,
        'cidade'    => Like::class,
        'situacao'  => Like::class,
    ];

    protected $allowedSorts = [
        'id',
        'nome',
        'cnpj',
        'created_at',
    ];

    // =============================================================
    // SCOPES
    // =============================================================

    /**
     * Scope para retornar apenas construtoras ativas (status = true).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAtivas($query)
    {
        return $query->where('status', true);
    }

    // =============================================================
    // BOOT → proteção contra CNPJ duplicado
    // =============================================================
    public static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            $model->cnpj = $model->cnpj ? preg_replace('/\D/', '', $model->cnpj) : null;
            $model->cep  = $model->cep  ? preg_replace('/\D/', '', $model->cep)  : null;

            if ($model->cnpj) {
                $query = static::where('cnpj', $model->cnpj);
                if ($model->exists) {
                    $query->where('id', '!=', $model->id);
                }
                if ($query->exists()) {
                    throw new \Exception('Este CNPJ já está cadastrado no sistema.');
                }
            }
        });
    }

    // =============================================================
    // ACCESSORS (mantidos iguais aos anteriores — só copiei os principais)
    // =============================================================
    public function getCnpjFormattedAttribute(): ?string
    {
        if (blank($this->cnpj) || strlen($this->cnpj) !== 14) {
            return $this->cnpj;
        }
        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($this->cnpj, 0, 2),
            substr($this->cnpj, 2, 3),
            substr($this->cnpj, 5, 3),
            substr($this->cnpj, 8, 4),
            substr($this->cnpj, 12, 2)
        );
    }

    public function getTelefoneFormattedAttribute(): ?string
    {
        $tel = preg_replace('/\D/', '', $this->telefone ?? '');
        return match (strlen($tel)) {
            11 => sprintf('(%s) %s-%s', substr($tel, 0, 2), substr($tel, 2, 5), substr($tel, 7)),
            10 => sprintf('(%s) %s-%s', substr($tel, 0, 2), substr($tel, 2, 4), substr($tel, 6)),
            default => $this->telefone,
        };
    }

    public function getSituacaoBadgeAttribute(): string
    {
        return match (strtoupper($this->situacao ?? '')) {
            'ATIVA'               => '<span class="badge badge-success">Ativa</span>',
            'BAIXADA', 'CANCELADA' => '<span class="badge badge-danger">Baixada</span>',
            'INAPTA', 'SUSPENSA'  => '<span class="badge badge-warning">Inapta/Suspensa</span>',
            default               => '<span class="badge badge-secondary">Desconhecida</span>',
        };
    }

    public function getEnderecoCompletoAttribute(): string
    {
        $parts = array_filter([
            $this->logradouro,
            $this->numero ? 'nº ' . $this->numero : null,
            $this->complemento,
            $this->bairro,
            $this->cidade ? $this->cidade . '/' . strtoupper($this->uf) : null,
            $this->cep ? 'CEP ' . substr($this->cep, 0, 5) . '-' . substr($this->cep, 5) : null,
        ]);
        return implode(', ', $parts) ?: 'Endereço não informado';
    }

    public function getSociosArrayAttribute(): array
    {
        return $this->socios ? array_map('trim', explode(',', $this->socios)) : [];
    }
}