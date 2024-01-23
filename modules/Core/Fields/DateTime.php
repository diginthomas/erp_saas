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

use Modules\Core\Contracts\Fields\Customfieldable;
use Modules\Core\Contracts\Fields\Dateable;
use Modules\Core\Facades\Format;
use Modules\Core\Fields\Dateable as DateableTrait;
use Modules\Core\Support\Placeholders\DateTimePlaceholder;
use Modules\Core\Table\DateTimeColumn;

class DateTime extends Field implements Customfieldable, Dateable
{
    use DateableTrait;

    /**
     * Field component.
     */
    public static $component = 'date-time-field';

    /**
     * Indicates if the field is searchable.
     *
     * @var null|callable
     */
    protected bool $searchable = false;

    /**
     * Initialize new DateTime instance class
     *
     * @param  string  $attribute field attribute
     * @param  string|null  $label field label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->rules(['nullable', 'date'])
            ->provideSampleValueUsing(fn () => date('Y-m-d H:i:s'))
            ->displayUsing(fn ($model, $value) => Format::dateTime($value));
    }

    /**
     * Create the custom field value column in database
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     * @param  string  $fieldId
     * @return void
     */
    public static function createValueColumn($table, $fieldId)
    {
        $table->dateTime($fieldId)->nullable();
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Modules\Core\Models\Model|null  $model
     * @return \Modules\Core\Support\Placeholders\DateTimePlaceholder
     */
    public function mailableTemplatePlaceholder($model)
    {
        return DateTimePlaceholder::make($this->attribute)
            ->value(fn () => $this->resolve($model))
            ->forUser($model?->user)
            ->description($this->label);
    }

    /**
     * Provide the column used for index
     */
    public function indexColumn(): DateTimeColumn
    {
        return new DateTimeColumn($this->attribute, $this->label);
    }
}
