<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Core\Application;
use Modules\Core\Database\Seeders\MailableTemplatesSeeder;
use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Facades\MailableTemplates;
use Modules\Core\Fields\CustomFieldFileCache;
use Modules\Core\Fields\FieldsManager;
use Modules\Core\Notifications\Notifications;
use Modules\Core\Resource\Resource;
use Modules\Core\Support\GateHelper;
use Modules\Core\Support\Timeline\Timeline;
use Modules\Core\Updater\UpdateFinalizer;
use Modules\Core\Workflow\Action as WorkflowAction;
use Modules\Core\Workflow\Workflows;
use Modules\Users\Support\TeamCache;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Finder\Finder;
use Tests\Fixtures\CalendarResource;
use Tests\Fixtures\EventResource;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication,
        CreatesUser,
        RefreshDatabase;

    /**
     * @var array
     */
    protected static $models;

    /**
     * Run a specific seeder before each test.
     *
     * @var string
     */
    protected $seeder = MailableTemplatesSeeder::class;

    /**
     * Setup the tests.
     */
    protected function setUp(): void
    {
        $_SERVER['_VERSION'] = Application::VERSION;

        Application::$resources = new Collection;
        Application::$provideToScript = [];

        Workflows::$triggers = [];
        Workflows::$eventOnlyListeners = [];
        Workflows::$processed = [];

        parent::setUp();

        $this->withoutMiddleware([
            \Modules\Core\Http\Middleware\PreventRequestsWhenUpdateNotFinished::class,
            \Modules\Core\Http\Middleware\PreventRequestsWhenMigrationNeeded::class,
        ]);

        $this->registerTestResources();
    }

    /**
     * Perform any work that should take place before the database has started refreshing.
     *
     * @return void
     */
    protected function beforeRefreshingDatabase()
    {
        $this->app['migrator']->path(base_path('tests/Migrations'));
    }

    /**
     * Tear down the tests.
     */
    protected function tearDown(): void
    {
        $this->tearDownChangelog();

        Resource::clearRegisteredResources();
        Timeline::forgetPinableSubjects();
        FieldsManager::flushCache();
        Notifications::enable();
        TeamCache::flush();
        CustomFieldFileCache::flush();
        MailableTemplates::autoDiscovery(true);
        MailableTemplates::flushCache();
        DatabaseState::forgetSeeders();
        WorkflowAction::disableExecutions(false);
        UpdateFinalizer::clearBeforeHooks();
        GateHelper::flushCache();
        \Spatie\Once\Cache::getInstance()->flush();

        parent::tearDown();
    }

    /**
     * Teardown changelog data.
     */
    protected function tearDownChangelog(): void
    {
        foreach (static::listModels() as $model) {
            if (method_exists($model, 'logsModelChanges') && $model::logsModelChanges()) {
                $model::$afterSyncCustomFieldOptions[$model] = [];
                $model::$beforeSyncCustomFieldOptions[$model] = [];

                $model::$changesPipes = [];
            }
        }
    }

    /**
     * Register the tests resources.
     */
    protected function registerTestResources(): void
    {
        Application::resources([
            EventResource::class,
            CalendarResource::class,
        ]);
    }

    /**
     * List the application available models.
     */
    protected function listModels(): array
    {
        if (! static::$models) {
            $paths = array_filter(array_values(array_map(function ($module) {
                $path = module_path($module->getLowerName(), 'Models');

                return is_dir($path) ? $path : null;
            }, Module::allEnabled())));

            $paths[] = app_path('Models');

            static::$models = collect((new Finder)->in($paths)->files()->name('*.php'))
                ->map(function ($model) {
                    if (str_contains($model, config('modules.paths.modules'))) {
                        return config('modules.namespace').'\\'.str_replace(
                            ['/', '.php'],
                            ['\\', ''],
                            Str::after($model->getRealPath(), realpath(config('modules.paths.modules')).DIRECTORY_SEPARATOR)
                        );
                    }

                    return app()->getNamespace().str_replace(
                        ['/', '.php'],
                        ['\\', ''],
                        Str::after($model->getRealPath(), realpath(app_path()).DIRECTORY_SEPARATOR)
                    );
                })->all();
        }

        return static::$models;
    }
}
