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

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;

/** @mixin \Modules\Core\Models\Filter */
class FilterResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'name' => $this->name,
            'identifier' => $this->identifier,
            'rules' => $this->rules,
            'user_id' => $this->user_id,
            'is_shared' => $this->is_shared,
            'is_system_default' => $this->is_system_default,
            'is_readonly' => $this->is_readonly,
            'defaults' => $this->defaults->map(fn ($default) => [
                'user_id' => $default->user_id,
                'view' => $default->view,
            ])->values(),
        ], $request);
    }
}
