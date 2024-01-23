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

namespace Modules\Activities\Providers;

use App\Http\View\FrontendComposers\Tab;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Modules\Activities\Console\Commands\SendActivitiesNotifications;
use Modules\Activities\Console\Commands\SyncNextActivityDate;
use Modules\Activities\Http\Resources\ActivityTypeResource;
use Modules\Activities\Listeners\StopRelatedOAuthCalendars;
use Modules\Activities\Listeners\TransferActivitiesUserData;
use Modules\Activities\Menu\TodaysActivitiesMetric;
use Modules\Activities\Models\Activity;
use Modules\Activities\Models\ActivityType;
use Modules\Activities\Observers\ActivityObserver;
use Modules\Activities\Observers\ActivityTransactionAwareObserver;
use Modules\Contacts\Resourceables\Company\Pages\DetailComponent as CompanyDetailComponent;
use Modules\Contacts\Resourceables\Contact\Pages\DetailComponent as ContactDetailComponent;
use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\MailableTemplates;
use Modules\Core\Facades\Menu;
use Modules\Core\Notifications\Notifications;
use Modules\Core\Settings\DefaultSettings;
use Modules\Core\Support\OAuth\Events\OAuthAccountDeleting;
use Modules\Core\SystemInfo;
use Modules\Deals\Resourceables\Pages\DetailComponent as DealDetailComponent;
use Modules\Users\Events\TransferringUserData;

class ActivitiesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Activities';

    protected string $moduleNameLower = 'activities';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        DatabaseState::register([
            \Modules\Activities\Database\State\EnsureDefaultFiltersArePresent::class,
            \Modules\Activities\Database\State\EnsureActivityTypesArePresent::class,
        ]);

        $this->app['events']->listen(OAuthAccountDeleting::class, StopRelatedOAuthCalendars::class);
        $this->app['events']->listen(TransferringUserData::class, TransferActivitiesUserData::class);

        DefaultSettings::add('send_contact_attends_to_activity_mail', false);
        DefaultSettings::addRequired('default_activity_type');

        $this->commands([
            SendActivitiesNotifications::class,
            SyncNextActivityDate::class,
        ]);

        SystemInfo::register('PREFERRED_DEFAULT_HOUR', $this->app['config']->get('activities.defaults.hour'));
        SystemInfo::register('PREFERRED_DEFAULT_MINUTES', $this->app['config']->get('activities.defaults.minutes'));

        $this->registerNotifications();
        $this->registerMailables();
        $this->registerRelatedRecordsDetailTab();
        Menu::metric(new TodaysActivitiesMetric);

        $this->app->booted($this->bootModule(...));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'),
            $this->moduleNameLower
        );
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $this->loadTranslationsFrom(module_path($this->moduleName, 'resources/lang'), $this->moduleNameLower);
    }

    /**
     * Boot the module.
     */
    protected function bootModule(): void
    {
        $this->registerResources();

        Activity::observe(ActivityObserver::class);
        Activity::observe(ActivityTransactionAwareObserver::class);

        Innoclapps::whenReadyForServing(function () {
            Innoclapps::booted($this->shareDataToScript(...));

            $this->scheduleTasks();
        });
    }

    /**
     * Schedule the module tasks.
     */
    protected function scheduleTasks(): void
    {
        /** @var \Illuminate\Console\Scheduling\Schedule */
        $schedule = $this->app->make(Schedule::class);

        $dueCommandName = 'notify-due-activities';
        $syncNextDateCommandName = 'sync-next-activity';

        if (Innoclapps::canRunProcess()) {
            $schedule->command('activities:notify')
                ->name($dueCommandName)
                ->everyMinute()
                ->withoutOverlapping(5);
            $schedule->command('activities:sync-next-date')
                ->name($syncNextDateCommandName)
                ->everyFiveMinutes()
                ->withoutOverlapping(5);
        } else {
            $schedule
                ->call(fn () => Artisan::call('activities:notify'))
                ->name($dueCommandName)
                ->everyMinute()
                ->withoutOverlapping(5);

            $schedule
                ->call(fn () => Artisan::call('activities:sync-next-date'))
                ->name($syncNextDateCommandName)
                ->everyFiveMinutes()
                ->withoutOverlapping(5);
        }
    }

    /**
     * Register the module available resources.
     */
    protected function registerResources(): void
    {
        Innoclapps::resources([
            \Modules\Activities\Resourceables\Activity::class,
            \Modules\Activities\Resourceables\ActivityType::class,
        ]);
    }

    /**
     * Register the activities module available notifications.
     */
    protected function registerNotifications(): void
    {
        Notifications::register([
            \Modules\Activities\Notifications\ActivityReminder::class,
            \Modules\Activities\Notifications\UserAssignedToActivity::class,
            \Modules\Activities\Notifications\UserAttendsToActivity::class,
        ]);
    }

    /**
     * Register the module available mailables.
     */
    protected function registerMailables(): void
    {
        MailableTemplates::register([
            \Modules\Activities\Mail\ActivityReminder::class,
            \Modules\Activities\Mail\ContactAttendsToActivity::class,
            \Modules\Activities\Mail\UserAssignedToActivity::class,
            \Modules\Activities\Mail\UserAttendsToActivity::class,
        ]);
    }

    /**
     * Register the module related tabs.
     */
    protected function registerRelatedRecordsDetailTab(): void
    {
        $tab = Tab::make('activities', 'activities-tab')->panel('activities-tab-panel')->order(15);

        ContactDetailComponent::registerTab($tab);
        CompanyDetailComponent::registerTab($tab);
        DealDetailComponent::registerTab($tab);
    }

    /**
     * Share data to script.
     */
    protected function shareDataToScript(): void
    {
        if (Auth::check()) {
            Innoclapps::provideToScript([
                'activities' => [
                    'defaults' => config('activities.defaults'),
                    'default_activity_type_id' => ActivityType::getDefaultType(),

                    'types' => ActivityTypeResource::collection(
                        ActivityType::withCommon()->orderBy('name')->get()
                    ),
                ],
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Get the publishable view paths.
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];

        foreach ($this->app['config']->get('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
