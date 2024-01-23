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

namespace Modules\Deals\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\MailableTemplates;
use Modules\Core\Facades\Menu;
use Modules\Core\Notifications\Notifications;
use Modules\Core\Settings\DefaultSettings;
use Modules\Core\Workflow\Workflows;
use Modules\Deals\Events\DealMovedToStage;
use Modules\Deals\Http\Resources\LostReasonResource;
use Modules\Deals\Http\Resources\PipelineResource;
use Modules\Deals\Listeners\LogDealMovedToStageActivity;
use Modules\Deals\Listeners\TransferDealsUserData;
use Modules\Deals\Menu\OpenDealsMetric;
use Modules\Deals\Models\Deal;
use Modules\Deals\Models\LostReason;
use Modules\Deals\Models\Pipeline;
use Modules\Deals\Observers\DealObserver;
use Modules\Users\Events\TransferringUserData;

class DealsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Deals';

    protected string $moduleNameLower = 'deals';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        $this->app['events']->listen(DealMovedToStage::class, LogDealMovedToStageActivity::class);
        $this->app['events']->listen(TransferringUserData::class, TransferDealsUserData::class);

        DatabaseState::register([
            \Modules\Deals\Database\State\EnsureDefaultFiltersArePresent::class,
            \Modules\Deals\Database\State\EnsureDefaultPipelineIsPresent::class,
        ]);

        DefaultSettings::add('allow_lost_reason_enter', true);
        DefaultSettings::add('lost_reason_is_required', true);

        Menu::metric(new OpenDealsMetric);

        $this->registerNotifications();
        $this->registerMailables();
        $this->registerWorkflowTriggers();

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
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Boot the module.
     */
    protected function bootModule(): void
    {
        $this->registerResources();
        Deal::observe(DealObserver::class);

        Innoclapps::whenReadyForServing(function () {
            Innoclapps::booted($this->shareDataToScript(...));
        });
    }

    /**
     * Register the documents module available resources.
     */
    public function registerResources(): void
    {
        Innoclapps::resources([
            \Modules\Deals\Resourceables\Deal::class,
            \Modules\Deals\Resourceables\Pipeline::class,
            \Modules\Deals\Resourceables\PipelineStage::class,
            \Modules\Deals\Resourceables\LostReason::class,
        ]);
    }

    /**
     * Register the documents module available notifications.
     */
    public function registerNotifications(): void
    {
        Notifications::register([
            \Modules\Deals\Notifications\UserAssignedToDeal::class,
        ]);
    }

    /**
     * Register the documents module available mailables.
     */
    public function registerMailables(): void
    {
        MailableTemplates::register([
            \Modules\Deals\Mail\UserAssignedToDeal::class,
        ]);
    }

    /**
     * Register the documents module available workflows.
     */
    public function registerWorkflowTriggers(): void
    {
        Workflows::triggers([
            \Modules\Deals\Workflow\Triggers\DealCreated::class,
            \Modules\Deals\Workflow\Triggers\DealStageChanged::class,
            \Modules\Deals\Workflow\Triggers\DealStatusChanged::class,
        ]);
    }

    /**
     * Share data to script.
     */
    public function shareDataToScript(): void
    {
        if (Auth::check()) {
            Innoclapps::provideToScript([
                'settings' => [
                    'allow_lost_reason_enter' => settings('allow_lost_reason_enter'),
                    'lost_reason_is_required' => settings('lost_reason_is_required'),
                ],

                'deal_fields_height' => settings('deal_fields_height'),

                'deals' => [
                    'tags_type' => Deal::TAGS_TYPE,
                    'pipelines' => PipelineResource::collection(
                        Pipeline::withCommon()
                            ->with('stages')
                            ->withVisibilityGroups()
                            ->visible()
                            ->userOrdered()
                            ->get()
                    ),
                    'lost_reasons' => LostReasonResource::collection(
                        LostReason::withCommon()->orderBy('name')->get()
                    ),
                ],
            ]);
        }
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
