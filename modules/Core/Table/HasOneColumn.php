<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.5
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2024 KONKORD DIGITAL
 */

namespace Modules\Core\Table;

use Illuminate\Database\Eloquent\Builder;

class HasOneColumn extends RelationshipColumn
{
    /**
     * Apply the order by query for the column
     */
    public function orderBy(Builder $query, string $direction): Builder
    {
        $relationInstance = $query->getModel()->{$this->relationName}();

        if (is_callable($this->orderByUsing)) {
            return call_user_func_array($this->orderByUsing, [$query, $direction, $this]);
        }

        $qualifiedRelationshipField = $relationInstance->qualifyColumn($this->relationField);

        return $query->orderBy(
            $relationInstance->getModel()->select($qualifiedRelationshipField)
                ->whereColumn($relationInstance->getQualifiedForeignKeyName(), $query->getModel()->getQualifiedKeyName())
                ->orderBy($qualifiedRelationshipField, $direction)
                ->limit(1),
            $direction
        );
    }
}
