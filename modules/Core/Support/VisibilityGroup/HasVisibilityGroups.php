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

namespace Modules\Core\Support\VisibilityGroup;

use Illuminate\Database\Eloquent\Builder;
use Modules\Users\Models\User;

interface HasVisibilityGroups
{
    public function isVisible(User $user): bool;

    public function scopeVisible(Builder $query, User $user): void;
}
