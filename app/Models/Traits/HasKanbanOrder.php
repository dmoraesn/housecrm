<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasKanbanOrder
{
    /**
     * O método "booted" do Trait.
     */
    protected static function bootHasKanbanOrder(): void
    {
        /**
         * Define a ordem inicial (máxima + 1) ao criar um novo item.
         */
        static::creating(function (Model $model) {
            $groupColumn = $model->getOrderGroupColumn();
            $status = $model->{$groupColumn} ?? $model->getOrderGroupKeys()[0];

            $model->order ??= self::nextOrderForGroup($status, $model);
        });

        /**
         * Reordena o item para o final da fila se o grupo (status) for alterado.
         */
        static::updating(function (Model $model) {
            $groupColumn = $model->getOrderGroupColumn();

            // Verifica se o campo de grupo (ex: 'status') foi modificado
            if ($model->isDirty($groupColumn)) {
                $newStatus = $model->{$groupColumn};
                // Atribui a nova ordem baseada no *novo* grupo
                $model->order = self::nextOrderForGroup($newStatus, $model);
            }
        });
    }

    /**
     * Implemente este método no seu Model para definir a coluna de agrupamento (ex: 'status').
     */
    abstract public function getOrderGroupColumn(): string;
    
    /**
     * Implemente este método no seu Model para fornecer as chaves de agrupamento (ex: Lead::FLUXO_VENDAS).
     */
    abstract public function getOrderGroupKeys(): array;

    /**
     * Calcula a próxima posição de 'order' para um determinado grupo.
     *
     * @param string $groupValue O valor da coluna de grupo (ex: 'novo').
     * @param Model $model A instância do modelo.
     * @return int
     */
    protected static function nextOrderForGroup(string $groupValue, Model $model): int
    {
        $groupColumn = $model->getOrderGroupColumn();
        
        // Pega a ordem máxima atual para esse grupo e soma 1
        return (int) $model::where($groupColumn, $groupValue)->max('order') + 1;
    }
}