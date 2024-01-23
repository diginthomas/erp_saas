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

namespace App\Installer;

use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Environment;
use Modules\Core\Facades\ChangeLogger;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Updater\Migration;

trait FinishesInstallation
{
    /**
     * Finalize the installation
     *
     * @throws \App\Installer\FailedToFinalizeInstallationException
     */
    protected function finalizeInstallation($currency, $country): void
    {
        $errors = [];

        try {
            Innoclapps::createStorageLink();
        } catch (\Exception) {
            $errors[] = 'Failed to create storage symlink.';
        }

        if (! Innoclapps::markAsInstalled()) {
            $errors[] = 'Failed to create the installed file. ('.Innoclapps::installedFileLocation().').';
        } else {
            $this->seedDatabaseState(['currency' => $currency, 'company_country_id' => $country]);
            Environment::capture();
        }

        if (count($errors)) {
            throw new FailedToFinalizeInstallationException(
                implode(PHP_EOL, $errors)
            );
        }

        Innoclapps::optimize();
    }

    /**
     * Save the database sate.
     */
    protected function seedDatabaseState(array $settings): void
    {
        // Ensure database state is up to date
        ChangeLogger::disabled(fn () => DatabaseState::seed());

        // Save default settings from installation
        settings($settings);
    }

    /**
     * Migrate the database
     */
    protected function migrate(): void
    {
        Migration::migrate();
    }
}
