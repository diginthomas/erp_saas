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

namespace App\Providers;

use App\Support\CommonPermissionsProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Settings\DefaultSettings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole() && ! empty($this->app['config']->get('app.cli_memory_limit'))) {
            \DetachedHelper::raiseMemoryLimit($this->app['config']->get('app.cli_memory_limit'));
        }

        if ($this->app['config']->get('app.force_ssl')) {
            URL::forceScheme('https');
        }

        Model::preventLazyLoading(! app()->isProduction());

        Schema::defaultStringLength(191);

        JsonResource::withoutWrapping();

        $this->app['config']->set('core.resources.permissions.common', CommonPermissionsProvider::class);

        DefaultSettings::add('disable_password_forgot', false);

        View::composer('components/layouts/auth', \Modules\Core\Http\View\Composers\AppComposer::class);
    }
}
