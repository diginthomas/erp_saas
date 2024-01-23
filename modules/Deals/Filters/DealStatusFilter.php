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

namespace Modules\Deals\Filters;

use Modules\Core\Filters\Select;
use Modules\Deals\Enums\DealStatus as StatusEnum;

class DealStatusFilter extends Select
{
    /**
     * Initialize Source class
     */
    public function __construct()
    {
        parent::__construct('status', __('deals::deal.status.status'));

        $this->options(collect(StatusEnum::cases())->mapWithKeys(function (StatusEnum $status) {
            return [$status->name => $status->label()];
        })->all());

        $this->query(function ($builder, $value, $condition, $sqlOperator) {
            return $builder->where($this->field, $sqlOperator['operator'], StatusEnum::find($value), $condition);
        });
    }
}
