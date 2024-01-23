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

namespace Modules\Core\Resource\Query;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Contracts\Resources\AcceptsCustomFields;
use Modules\Core\Facades\Innoclapps;

class EloquentQueryBuilder extends Builder
{
    /**
     * Set the relationships that should be eager loaded.
     *
     * The method ensures when regularly using $query->with('resourceRelationWithCustomFields')
     * is loading the resource optionable custom fields to avoid eager loading violation.
     *
     * @param  string|array  $relations
     * @param  string|\Closure|null  $callback
     * @return $this
     */
    public function with($relations, $callback = null)
    {
        if ($callback instanceof \Closure) {
            $return = parent::with($relations, $callback);
        } else {
            $return = parent::with(...func_get_args());
        }

        foreach (array_keys($this->eagerLoad) as $relation) {
            if (! str_contains($relation, '.')) {
                $relatedModel = $this->getModel()->{$relation}()->getModel();
                $relatedResource = Innoclapps::resourceByModel($relatedModel);

                if ($relatedResource instanceof AcceptsCustomFields) {
                    $eagerLoad = $relatedResource->customFields()->optionable()->pluck('relationName');

                    parent::with(
                        $eagerLoad->map(fn ($customFieldRelation) => $relation.'.'.$customFieldRelation)->all()
                    );
                }
            }
        }

        return $return;
    }
}
