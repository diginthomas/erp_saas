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

namespace Modules\Core\Updater;

use Modules\Core\Application;
use Modules\Core\Facades\Innoclapps;

class UpdateFinalizer
{
    protected static array $before = [];

    /**
     * Run the update finalizer.
     */
    public function run(): bool
    {
        if (! $this->needed()) {
            return false;
        }

        static::runBeforeHooks();

        Innoclapps::clearCache();

        $this->runPatchers();

        settings([
            '_version' => Application::VERSION,
            '_last_updated_date' => date('Y-m-d H:i:s'),
            '_updated_from' => $this->getCachedCurrentVersion(),
        ]);

        $this->optimizeIfNeeded();

        if (config('updater.restart_queue')) {
            Innoclapps::restartQueue();
        }

        return true;
    }

    /**
     * Check whether finalization of the update is needed.
     */
    public function needed(): bool
    {
        return version_compare(
            $this->getCachedCurrentVersion(),
            Application::VERSION, '<'
        );
    }

    /**
     * Register custom action to execute before running the finalizer.
     */
    public static function before(callable $callback): void
    {
        static::$before[] = $callback;
    }

    /**
     * Run the before update finalizer hooks.
     */
    protected static function runBeforeHooks(): void
    {
        foreach (static::$before as $callable) {
            call_user_func($callable);
        }

        static::clearBeforeHooks();
    }

    /**
     * Clear the registered before hooks.
     */
    public static function clearBeforeHooks(): void
    {
        static::$before = [];
    }

    /**
     * Get the cached current version.
     */
    public function getCachedCurrentVersion(): string
    {
        return settings('_version') ?: ($_SERVER['_VERSION'] ?? '1.0.7');
    }

    /**
     * Optimize the application.
     */
    protected function optimizeIfNeeded(): void
    {
        if (! app()->runningUnitTests() && app()->isProduction()) {
            Innoclapps::optimize();
        }
    }

    /**
     * Execute the updates patchers.
     */
    protected function runPatchers(): void
    {
        app(Migration::class)->patchers()
            // Get all the versions starting from current cached (excluding current cached as this one is already executed)
            // between the latest available update for the current version (including current)
            ->filter(
                fn ($patch) => ! (version_compare($patch->version(), $this->getCachedCurrentVersion(), '<=') ||
                    version_compare($patch->version(), Application::VERSION, '>'))
            )
            ->filter->shouldRun()
            ->each->run();
    }
}
