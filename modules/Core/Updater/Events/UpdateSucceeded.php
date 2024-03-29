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

use Modules\Core\Updater\Release;

class UpdateSucceeded
{
    /**
     * Initialize new UpdateSucceeded instance.
     */
    public function __construct(public Release $release)
    {
    }

    /**
     * Get the release.
     */
    public function getRelease(): Release
    {
        return $this->release;
    }

    /**
     * Get the new version.
     */
    public function getVersionUpdatedTo(): string
    {
        return $this->release->getVersion();
    }
}
