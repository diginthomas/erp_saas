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

namespace Modules\Core\Fields;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Contracts\Fields\Customfieldable;
use Modules\Core\Facades\Fields;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomFieldOption;
use Modules\Core\Models\Filter;
use Modules\Core\QueryBuilder\Parser;
use Modules\Core\Resource\Resource;

class CustomFieldService
{
    /**
     * Get custom field by given ID.
     */
    public function find(int $id): CustomField
    {
        return CustomField::find($id);
    }

    /**
     * Get the given resource custom fields.
     */
    public static function forResource(string|Resource $resourceName): CustomFieldCollection
    {
        if ($resourceName instanceof Resource) {
            $resourceName = $resourceName->name();
        }

        return CustomFieldFileCache::get()->where('resource_name', $resourceName);
    }

    /**
     * Create new custom field in storage.
     */
    public function create(array $attributes): CustomField
    {
        $field = CustomField::create([
            'resource_name' => $attributes['resource_name'],
            'label' => $attributes['label'],
            'field_type' => $attributes['field_type'],
            'field_id' => $attributes['field_id'],
            'is_unique' => array_key_exists('is_unique', $attributes) ? $attributes['is_unique'] : null,
        ]);

        $this->createColumn($field);

        if ($field->isOptionable()) {
            return $this->createOptions($attributes['options'], $field)->load('options');
        }

        return $field;
    }

    /**
     * Create options for the given field.
     */
    public function createOptions(array $options, CustomField $field): CustomField
    {
        $options = isset($options[0]) ? $options : [$options];

        $this->checkOptions($options)->each(fn (array $option, int $index) => $field->options()->create([
            'name' => $option['name'],
            'display_order' => $option['display_order'] ?? $index + 1,
            'swatch_color' => $option['swatch_color'] ?? null,
        ]));

        return $field;
    }

    /**
     * Update the field in storage.
     */
    public function update(array $attributes, int $id): CustomField
    {
        $field = $this->find($id);
        $unmarkAsUnique = Arr::pull($attributes, 'is_unique') === false && $field->is_unique;

        $field->fill(array_merge($attributes, [
            'is_unique' => $unmarkAsUnique ? false : $field->is_unique,
        ]))->save();

        if ($field->isOptionable()) {
            $this->handleFieldOptionsUpdate($field, $attributes['options']);
        }

        if ($unmarkAsUnique) {
            $this->dropUniqueIndex($field);
        }

        return $field->load('options');
    }

    /**
     * Delete custom field by given ID.
     */
    public function delete(int $id): bool
    {
        $this->dropColumn($field = $this->find($id));

        $deleted = $field->delete();

        $this->handleDeletedFieldFiltersRules($field);

        return $deleted;
    }

    /**
     * Handle the field options update.
     */
    protected function handleFieldOptionsUpdate(CustomField $field, array $options): void
    {
        $optionsBeforeUpdate = $field->options;

        $this->checkOptions($options)->each(function (array $option, int $index) use ($field, $optionsBeforeUpdate) {
            $attributes = [
                'name' => $option['name'],
                'display_order' => $option['display_order'] ?? $index + 1,
                'swatch_color' => $option['swatch_color'] ?? null,
            ];

            if (isset($option['id'])) {
                $optionsBeforeUpdate->find($option['id'])->fill($attributes)->save();
            } else {
                $field->options()->create($attributes);
            }
        });

        $optionsBeforeUpdate->filter(function (CustomFieldOption $option) use ($options) {
            return ! in_array($option->id, Arr::pluck($options, 'id'));
        })->each(function (CustomFieldOption $option) use ($field) {
            if ($field->isMultiOptionable()) {
                $option->delete();
            } else {
                // Update constraint
                $field->resource()->newQueryWithTrashed()->update([$field->field_id => null]);
            }
        });
    }

    /**
     * Sync the custom field options for the given model
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function syncOptionsForModel($model, Customfieldable&Field $field, array $ids, string $action): void
    {
        $callbackAttributes = [$model, $field, $ids, $action];

        collect($model::$beforeSyncCustomFieldOptions[$model::class] ?? [])->each->__invoke(
            ...$callbackAttributes
        );

        $model->{$field->customField->relationName}()->syncWithPivotValues(
            $ids,
            ['custom_field_id' => $field->customField->id]
        );

        collect($model::$afterSyncCustomFieldOptions[$model::class] ?? [])->each->__invoke(
            ...$callbackAttributes
        );
    }

    /**
     * Create the custom field in database.
     */
    protected function createColumn(CustomField $field): void
    {
        $fromField = Fields::customFieldable()[$field->field_type];

        Schema::table($field->resource()->newModel()->getTable(), function (Blueprint $table) use ($fromField, $field) {
            $fromField['className']::createValueColumn($table, $field->field_id);

            if ($field->is_unique === true && $fromField['uniqueable']) {
                $table->unique($field->field_id, $field->uniqueIndexName());
            }
        });
    }

    /**
     * Drop the column related to the given custom field.
     */
    protected function dropColumn(CustomField $field): void
    {
        $relatedModel = $field->resource()->newModel();
        $table = $relatedModel->getTable();

        if (! Schema::hasColumn($table, $field->field_id)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($field, $relatedModel) {
            if ($field->isOptionable() && ! app()->runningUnitTests()) {
                $fieldIndexes = DB::listIndexes($relatedModel, $field->field_id);

                collect($fieldIndexes)->each(function (array $index) use ($table) {
                    $table->dropForeign($index['name']);
                });
            }

            $table->dropColumn($field->field_id);
        });
    }

    /**
     * Drop the unique index for the given field.
     */
    protected function dropUniqueIndex(CustomField $field): void
    {
        Schema::table($field->resource()->newModel()->getTable(), function (Blueprint $table) use ($field) {
            $table->dropUnique($field->uniqueIndexName());
        });
    }

    /**
     * Handle the deleted custom field filter rules.
     */
    protected function handleDeletedFieldFiltersRules(CustomField $field): void
    {
        // When model with custom fields is deleted, we will get the filters
        // which most likely are using custom field and remove them from the query object
        Filter::where('rules', 'like', '%'.$field->field_id.'%')
            ->get()
            ->each(function (Filter $filter) use ($field) {
                $rules = Arr::toObject($filter->rules);

                if (Parser::validate($rules)) {
                    $rules->children = $this->handleDeletedFieldFilterRules($rules->children, $field);

                    $filter->fill(['rules' => (array) $rules ?? []])->save();
                }
            });
    }

    /**
     * Handle the deleted field rules.
     */
    protected function handleDeletedFieldFilterRules(array &$rules, CustomField $field): array
    {
        foreach ($rules as $key => $rule) {
            if (Parser::isNested($rule)) {
                $rule->query->children = $this->handleDeletedFieldFilterRules($rule->query->children, $field);
            } else {
                if ($rule->query->rule == $field->field_id ||
                    (isset($rule->query->operand) && $rule->query->operand == $field->field_id)) {
                    unset($rules[$key]);
                }
            }

            // If the current rule in the loop is group, we will check if the
            // group is empty and if yes, we will actually remove the group from
            // the rules object as it won't be needed.
            if ($rule->type === 'group' && empty($rule->query->children)) {
                unset($rules[$key]);
            }
        }

        return array_values($rules);
    }

    /**
     * Perform a checks on the given options.
     * The option name must not be empty as well all options must be unique.
     */
    protected function checkOptions(array $options): Collection
    {
        return collect($options)->reject(
            fn ($option) => empty($option['name'])
        )->unique('name');
    }
}
