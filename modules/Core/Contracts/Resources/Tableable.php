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

namespace Modules\Core\Contracts\Resources;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Table\Table;

interface Tableable
{
    /**
     * Provide the resource table class instance.
     */
    public function table(Builder $query, ResourceRequest $request): Table;
}
