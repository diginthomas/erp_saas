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

namespace Modules\Contacts\Fields;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Modules\Contacts\Http\Resources\ContactResource;
use Modules\Contacts\Models\Contact as ContactModel;
use Modules\Contacts\Resourceables\Contact\Contact;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\ConfiguresOptions;
use Modules\Core\Fields\MorphToMany;
use Modules\Core\Fields\Selectable;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Settings\Utilities\Arr;
use Modules\Core\Support\HasOptions;
use Modules\Core\Table\MorphToManyColumn;

class Contacts extends MorphToMany
{
    use ConfiguresOptions, HasOptions, Selectable;

    /**
     * Multi select custom component
     */
    public static $component = 'select-multiple-field';

    public ?int $order = 1000;

    protected static Contact $resource;

    /**
     * Create new instance of Contacts field
     *
     * @param  string  $companies
     * @param  string  $label Custom label
     */
    public function __construct($relation = 'contacts', $label = null)
    {
        parent::__construct($relation, $label ?? __('contacts::contact.contacts'));

        static::$resource = Innoclapps::resourceByName('contacts');

        $this->labelKey('display_name')
            ->valueKey('id')
            // Used for export
            ->displayUsing(
                fn ($model) => $model->contacts->pluck('displayName')->implode(', ')
            )
            ->onOptionClickRedirectTo('/contacts/{id}')
            ->eachOnNewLine()
            ->excludeFromZapierResponse()
            ->async('/contacts/search')
            ->lazyLoad('/contacts', ['order' => 'created_at|desc'])
            ->tapIndexColumn(function (MorphToManyColumn $column) {
                $column->wrap()->queryAs('first_name')->select(['last_name']); // For display_name append
            })
            ->provideSampleValueUsing(fn () => 'Contact Full Name, Other Contact Full Name')
            ->fillUsing(function (Model $model, string $attribute, ResourceRequest $request, mixed $value) {
                return ! is_null($value) ? $this->fillCallback($model, $this->parseValue($value, $request)) : null;
            })->resolveForJsonResourceUsing(function (Model $model, string $attribute) {
                if ($model->relationLoaded($this->morphToManyRelationship)) {
                    return [
                        $attribute => ContactResource::collection($this->resolve($model)),
                    ];
                }
            });
    }

    /**
     * Provide the column used for index
     */
    public function indexColumn(): MorphToManyColumn
    {
        return new MorphToManyColumn(
            $this->morphToManyRelationship,
            $this->labelKey,
            $this->label
        );
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
        $model = null;

        // ID provided?
        if (is_numeric($value)) {
            $model = static::$resource->newQuery()->find($value);
        } elseif ($value) {
            $value = trim($value);

            $model = static::$resource->newQueryWithTrashed()
                ->where(ContactModel::nameQueryExpression(), $value)
                ->first() ?: static::$resource->findByEmail($value, static::$resource->newQueryWithTrashed());

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
