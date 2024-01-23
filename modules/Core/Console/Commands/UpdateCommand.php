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

namespace Modules\Core\Console\Commands;

use App\Installer\RequirementsChecker;
use Illuminate\Console\Command;
use Modules\Core\Updater\Updater;
use Modules\Users\Models\User;
use Throwable;

class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updater:update {--key= : Purchase key} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the application to the latest available version.';

    /**
     *  Execute the console command.
     */
    public function handle(RequirementsChecker $requirements, Updater $updater)
    {
        if ($requirements->fails('zip')) {
            $this->error(__('core::update.update_zip_is_required'));

            return 1;
        }

        $this->info('Configuring purchase key.');
        $updater->usePurchaseKey($this->option('key') ?: '');

        if (! $updater->isNewVersionAvailable()) {
            $this->info('The latest version '.$updater->getVersionInstalled().' is already installed.');

            return;
        }

        $force = $this->option('force');

        if (! $force && User::anyActiveRecently()) {
            $this->info('Skipping update, the last active user was in less than 30 minutes, try later.');

            return;
        }

        $this->info('Preparing update.');

        $this->down();

        if (! $this->getLaravel()->runningUnitTests()) {
            $this->info('Increasing PHP config values.');

            $this->increasePhpIniValues();
        }

        $this->info('Performing update, this may take a while.');

        try {
            $updater->update($updater->getVersionAvailable());
        } catch (Throwable $e) {
            $this->up();

            throw $e;
        } finally {
            $this->up();
        }
    }

    /**
     * Bring the application out of maintenance mode
     */
    protected function up(): void
    {
        $this->info('Bringing the application out of maintenance mode.');
        $this->callSilently('up');
    }

    /**
     * Put the application into maintenance mode
     */
    protected function down(): void
    {
        $this->info('Putting the application into maintenance mode.');
        $this->callSilently('down', ['--render' => 'core::errors.updating']);
    }

    /**
     * Increase php.ini values if possible
     */
    protected function increasePhpIniValues(): void
    {
        \DetachedHelper::raiseMemoryLimit('256M');

        if (function_exists('set_time_limit')) {
            set_time_limit(240);
        } else {
            @ini_set('max_execution_time', 240);
        }
    }
}
