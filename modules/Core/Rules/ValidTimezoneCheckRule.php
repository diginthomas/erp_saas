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

namespace Modules\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTimezoneCheckRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! empty($value) && ! in_array($value, tz()->all())) {
            $fail('validation.timezone')->translate();
        }
    }
}
