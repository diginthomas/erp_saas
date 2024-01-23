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

namespace Modules\Users\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\MailableTemplates;
use Modules\Core\Notifications\Notifications;
use Modules\Users\Http\Resources\UserResource;
use Modules\Users\Models\User;

class UsersServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Users';

    protected string $moduleNameLower = 'users';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        $this->registerNotifications();
        $this->registerMailables();

        $this->app->booted($this->bootModule(...));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        $this->booting(function () {
            Gate::before(fn ($user, $ability) => $user->isSuperAdmin() ? true : null);
            Gate::define('is-super-admin', fn ($user) => $user->isSuperAdmin());
            Gate::define('access-api', fn ($user) => $user->hasApiAccess());
        });
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
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower.'-module-views']);

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

        Innoclapps::whenReadyForServing(function () {
            Innoclapps::booted($this->shareDataToScript(...));
        });
    }

    /**
     * Register the users available resources.
     */
    public function registerResources(): void
    {
        Innoclapps::resources([
            \Modules\Users\Resourceables\User::class,
        ]);
    }

    /**
     * Register the documents module available notifications.
     */
    public function registerNotifications(): void
    {
        Notifications::register([
            \Modules\Users\Notifications\ResetPassword::class,
            \Modules\Users\Notifications\UserMentioned::class,
        ]);
    }

    /**
     * Register the documents module available mailables.
     */
    public function registerMailables(): void
    {
        MailableTemplates::register([
            \Modules\Users\Mail\ResetPassword::class,
            \Modules\Users\Mail\InvitationCreated::class,
            \Modules\Users\Mail\UserMentioned::class,
        ]);
    }

    /**
     * Share data to script.
     */
    public function shareDataToScript(): void
    {
        if (Auth::check()) {
            Innoclapps::provideToScript([
                'user_id' => Auth::id(),
                'users' => UserResource::collection(
                    User::withCommon()->get()
                ),
                'invitation' => [
                    'expires_after' => config('users.invitation.expires_after'),
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
