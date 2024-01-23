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

use Illuminate\Support\Facades\Broadcast;
use Modules\Deals\Models\Deal;

Broadcast::channel('Modules.Deals.Models.Deal.{dealId}', function ($user, $dealId) {
    return $user->can('view', Deal::findOrFail($dealId));
});
