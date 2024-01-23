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
use Modules\Core\Rules\SwatchColorRule;

class ColorSwatch extends Field implements Customfieldable
{
    /**
     * Field component.
     */
    public static $component = 'color-swatch-field';

    /**
     * Indicates if the field is searchable.
     *
     * @var null|callable
     */
    protected bool $searchable = false;

    /**
     * Initialize new ColorSwatch instance class
     *
     * @param  string  $attribute field attribute
     * @param  string|null  $label field label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->rules(['nullable', new SwatchColorRule])->provideSampleValueUsing(fn () => '#374151');
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
        $table->string($fieldId, 7)->nullable();
    }
}
