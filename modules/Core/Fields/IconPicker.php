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

class IconPicker extends Field
{
    /**
     * Field component.
     */
    public static $component = 'icon-picker-field';

    /**
     * Indicates if the field is searchable.
     *
     * @var null|callable
     */
    protected bool $searchable = false;
}
