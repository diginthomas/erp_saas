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

namespace Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Core\Support\Zapier\Zapier as BaseZapier;

/**
 * @method static void processQueue()
 * @method static array modelEvents()
 * @method static static queue(string $action, array|int $records, \Modules\Core\Resource\Resource $resource)
 *
 * @mixin \Modules\Core\Support\Zapier\Zapier
 */
class Zapier extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return BaseZapier::class;
    }
}
