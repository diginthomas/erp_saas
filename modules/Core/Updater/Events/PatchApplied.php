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

namespace Modules\Core\Updater\Events;

use Modules\Core\Updater\Patch;

class PatchApplied
{
    /**
     * Initialize new PatchApplied instance.
     */
    public function __construct(public Patch $patch)
    {
    }

    /**
     * Get the patch that was applied.
     */
    public function getPatch(): Patch
    {
        return $this->patch;
    }
}
