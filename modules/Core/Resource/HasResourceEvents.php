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

namespace Modules\Core\Resource;

use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;

trait HasResourceEvents
{
    /**
     * Handle the "beforeCreate" resource record hook.
     */
    public function beforeCreate(Model $model, ResourceRequest $request): void
    {
    }

    /**
     * Handle the "afterCreate" resource record hook.
     */
    public function afterCreate(Model $model, ResourceRequest $request): void
    {
    }

    /**
     * Handle the "beforeUpdate" resource record hook.
     */
    public function beforeUpdate(Model $model, ResourceRequest $request): void
    {
    }

    /**
     * Handle the "afterUpdate" resource record hook.
     */
    public function afterUpdate(Model $model, ResourceRequest $request): void
    {
    }

    /**
     * Handle the "beforeDelete" resource record hook.
     */
    public function beforeDelete(Model $model, ResourceRequest $request): void
    {
    }

    /**
     * Handle the "afterDelete" resource record hook.
     */
    public function afterDelete(Model $model, ResourceRequest $request): void
    {
    }
}
