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

namespace Modules\Deals\Fields;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Arr;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\ConfiguresOptions;
use Modules\Core\Fields\MorphToMany;
use Modules\Core\Fields\Selectable;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Support\HasOptions;
use Modules\Core\Table\MorphToManyColumn;
use Modules\Deals\Http\Resources\DealResource;
use Modules\Deals\Resourceables\Deal;

class Deals extends MorphToMany
{
    use ConfiguresOptions, HasOptions, Selectable;

    /**
     * Multi select custom component
     */
    public static $component = 'select-multiple-field';

    public ?int $order = 999;

    protected static Deal $resource;

    /**
     * Create new instance of Deals field
     *
     * @param  string  $relation
     * @param  string  $label Custom label
     */
    public function __construct($relation = 'deals', $label = null)
    {
        parent::__construct($relation, $label ?? __('deals::deal.deals'));

        static::$resource = Innoclapps::resourceByName('deals');

        $this->labelKey('name')
            ->valueKey('id')
            ->excludeFromExport()
            ->excludeFromImport()
            ->onOptionClickRedirectTo('/deals/{id}')
            ->eachOnNewLine()
            ->excludeFromZapierResponse()
            ->async('/deals/search')
            ->lazyLoad('/deals', ['order' => 'created_at|desc'])
            ->provideSampleValueUsing(fn () => [1, 2])
            ->fillUsing(function (Model $model, string $attribute, ResourceRequest $request, mixed $value) {
                return ! is_null($value) ? $this->fillCallback($model, $this->parseValue($value, $request)) : null;
            })->resolveForJsonResourceUsing(function (Model $model, string $attribute) {
                if ($model->relationLoaded($this->morphToManyRelationship)) {
                    return [
                        $attribute => DealResource::collection($this->resolve($model)),
                    ];
                }
            });
    }

    /**
     * Provide the column used for index
     */
    public function indexColumn(): MorphToManyColumn
    {
        return (new MorphToManyColumn(
            $this->morphToManyRelationship,
            $this->labelKey,
            $this->label
        ))->wrap();
    }

    /**
     * Parse the given value for storage.
     */
    protected function parseValue($value, ResourceRequest $request): Collection
    {
        // Perhaps int e.q. when ID provided?
        $value = is_string($value) ? explode(',', $value) : Arr::wrap($value);
        $collection = new Collection([]);

        foreach ($value as $id) {
            if ($model = $this->getModelFromValue($id, $request)) {
                $collection->push($model);
            }
        }

        return $collection;
    }

    /**
     * Get model instance from the given ID and ensure it's authorized to view before syncing.
     */
    protected function getModelFromValue(int|string|null $value, ResourceRequest $request): ?EloquentModel
    {
        // ID provided?
        if (is_numeric($value)) {
            $model = static::$resource->newQuery()->find($value);
        } elseif ($value) {
            $model = static::$resource->newQueryWithTrashed()->where('name', trim($value))->first();

            if ($model?->trashed()) {
                $model->restore();
            }
        }

        return $model && $request->user()->can('view', $model) ? $model : null;
    }

    /**
     * Get the fill callback.
     */
    protected function fillCallback(Model $model, Collection $ids)
    {
        return function () use ($model, $ids) {
            if ($model->wasRecentlyCreated) {
                if (count($ids) > 0) {
                    $model->{$this->morphToManyRelationship}()->attach($ids);
                }
            } else {
                $model->{$this->morphToManyRelationship}()->sync($ids);
            }
        };
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'options' => [],
            'labelKey' => $this->labelKey,
            'valueKey' => $this->valueKey,
        ]);
    }
}
