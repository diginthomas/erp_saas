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

namespace Modules\Core\Support\Google\Concerns;

trait HasDecodeableBody
{
    /**
     * @return string
     */
    public function getDecodedBody($content)
    {
        return str_replace('_', '/', str_replace('-', '+', $content));
    }
}
