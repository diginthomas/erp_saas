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

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return array_merge($this->baseRules(), ['name' => 'required|string|max:191']);
    }

    /**
     * Get the base validation rules that apply to the request.
     */
    protected function baseRules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'cards.*.key' => 'sometimes|required',
            'cards.*.order' => 'sometimes|numeric',
            'cards.*.enabled' => 'sometimes|boolean',
            'is_default' => 'sometimes|boolean',
        ];
    }
}
