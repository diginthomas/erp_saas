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

namespace Modules\Core\Support\Macros\Arr;

class ToObject
{
    public function __invoke($array)
    {
        if (! is_array($array) && ! is_object($array)) {
            return new \stdClass();
        }

        return json_decode(json_encode((object) $array));
    }
}
