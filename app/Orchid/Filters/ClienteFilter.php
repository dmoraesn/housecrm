<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class ClienteFilter extends Filter
{
    public function name(): string
    {
        return 'Cliente';
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('cliente', 'like', "%{$this->request->get('cliente')}%");
    }

    public function display(): iterable
    {
        return [
            Field::text('cliente')
                ->title('Buscar por cliente')
                ->placeholder('Nome do cliente'),
        ];
    }
}