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

abstract class MorphToMany extends Field
{
    /**
     * Field relationship name
     */
    public string $morphToManyRelationship;

    /**
     * Indicates if the field is excluded from index query.
     */
    public bool $isExcludedFromIndexQuery = true;

    /**
     * Indicates if the field is searchable.
     *
     * @var null|callable
     */
    protected bool $searchable = false;

    /**
     * Initialize new MorphToMany instance class
     *
     * @param  string  $attribute
     * @param  string|null  $label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->morphToManyRelationship = $attribute;

        $this->fillUsing(function () {
        });
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Modules\Core\Models\Model|null  $model
     */
    public function mailableTemplatePlaceholder($model)
    {
        return null;
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'morphToManyRelationship' => $this->morphToManyRelationship,
        ]);
    }
}
