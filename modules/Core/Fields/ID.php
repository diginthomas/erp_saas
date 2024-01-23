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

use Modules\Core\Table\Column;

class ID extends Field
{
    /**
     * Field component.
     */
    public static $component = 'id-field';

    /**
     * Initialize new ID instance.
     */
    public function __construct(string $attribute = 'id', ?string $label = null)
    {
        parent::__construct($attribute, $label ?: __('core::app.id'));

        $this->exceptOnForms()
            ->readOnly(true)
            ->useSearchColumn([$this->attribute => '='])
            ->tapIndexColumn(fn (Column $column) => $column
                ->width('100px')
                ->minWidth('100px')
                ->centered()
            );
    }

    /**
     * Resolve the field value for export
     *
     * @param  \Modules\Core\Models\Model  $model
     */
    public function resolveForExport($model)
    {
        return null;
    }
}
