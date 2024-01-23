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

namespace Modules\Core\Providers;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Modules\Core\Criteria\RequestCriteria;
use Modules\Core\Settings\ConfigOverrides;
use Modules\Core\Settings\ConfigRepository;
use Modules\Core\Settings\Contracts\Manager as ManagerContract;
use Modules\Core\Settings\Contracts\Store as StoreContract;
use Modules\Core\Settings\DefaultSettings;
use Modules\Core\Settings\SettingsManager;

class BootstrapServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Core';

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Tmp for v1.1.7
        if (is_file(config_path('innoclapps.php'))) {
            $this->deleteConflictedLegacyFiles();
            exit(header('Location: /dashboard'));
        }

        $this->registerSettings();
    }

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/settings.php'),
            'settings'
        );

        ConfigOverrides::add($this->app['config']->get('settings.override', []));

        $this->registerMacros();
        $this->registerDefaultSettings();
        $this->setUserDefinedExtensionsInConfig();
        $this->configureDefaultBroadcastConnection();
    }

    /**
     * Register the settings feature.
     */
    protected function registerSettings(): void
    {
        $this->app->singleton(ManagerContract::class, function ($app) {
            $manager = new SettingsManager($app);

            foreach ($app['config']->get('settings.drivers', []) as $driver => $params) {
                $manager->registerStore($driver, $params);
            }

            return $manager;
        });

        $this->app->extend('config', function (Repository $repository) {
            return new ConfigRepository($repository->all());
        });

        $this->app->singleton(StoreContract::class, function ($app) {
            return $app[ManagerContract::class]->driver();
        });
    }

    /**
     * Register the default settings.
     */
    protected function registerDefaultSettings(): void
    {
        DefaultSettings::addRequired('date_format', 'F j, Y');
        DefaultSettings::addRequired('time_format', 'H:i');
        DefaultSettings::add('block_bad_visitors', false);
        DefaultSettings::addRequired('currency', 'USD');
        DefaultSettings::addRequired(
            'allowed_extensions',
            'jpg, jpeg, png, gif, svg, pdf, aac, ogg, oga, mp3, wav, mp4, m4v,mov, ogv, webm, zip, rar, doc, docx, txt, text, xml, json, xls, xlsx, odt, csv, ppt, pptx, ppsx, ics, eml'
        );
    }

    /**
     * Set the application default broadcast connection.
     */
    protected function configureDefaultBroadcastConnection(): void
    {
        $config = $this->app['config'];

        $keyOptions = Arr::only(
            $config->get('broadcasting.connections.pusher'),
            ['key', 'secret', 'app_id']
        );

        $pusherEnabled = count(array_filter($keyOptions)) === count($keyOptions);

        $pusherOptions = $config->get('broadcasting.connections.pusher.options');

        $config->set('broadcasting.default', $pusherEnabled ? 'pusher' : 'null');

        if ($pusherEnabled && ! str_starts_with($pusherOptions['host'], 'api-'.$pusherOptions['cluster'])) {
            $config->set(
                'broadcasting.connections.pusher.options.host',
                'api-'.$pusherOptions['cluster'].'.pusher.com'
            );
        }
    }

    /**
     * Set application allowed media extensions in config.
     */
    protected function setUserDefinedExtensionsInConfig(): void
    {
        // Replace dots with empty in case the user add dot in the extension name
        $this->app['config']->set('mediable.allowed_extensions', array_map(
            fn ($extension) => trim(Str::replaceFirst('.', '', $extension)),
            // use the get method because of 1.0.6 changes in settings function, fails on update if not used
            explode(',', settings()->get('allowed_extensions'))
        ));
    }

    /**
     * Register application macros.
     */
    public function registerMacros(): void
    {
        Str::macro('isBase64Encoded', new \Modules\Core\Support\Macros\Str\IsBase64Encoded);
        Str::macro('clickable', new \Modules\Core\Support\Macros\Str\ClickableUrls);

        Arr::macro('toObject', new \Modules\Core\Support\Macros\Arr\ToObject);

        Request::macro('isSearching', fn () => ! is_null($this->get(RequestCriteria::QUERY_KEY)));
        Request::macro('isZapier', fn () => $this->header('user-agent') === 'Zapier');
        Request::macro('getWith', fn () => Str::of($this->get('with', ''))->explode(';')->filter()->all());
        Request::macro('isForTimeline', fn () => $this->boolean('timeline'));

        TestResponse::macro('assertActionUnauthorized', fn () => $this->assertJson(['error' => __('users::user.not_authorized')]));
        TestResponse::macro('assertActionOk', fn () => $this->assertJsonMissingExact(['error' => __('users::user.not_authorized')]));

        DB::macro('listIndexes', new \Modules\Core\Support\Macros\Database\ListIndexes);

        Filesystem::macro('deepCleanDirectory', new \Modules\Core\Support\Macros\Filesystem\DeepCleanDirectory);

        \Modules\Core\Support\Macros\Criteria\QueryCriteria::register();

        URL::macro('asAppUrl', fn (string $extra = '') => rtrim(config('app.url'), '/').($extra ? '/'.$extra : ''));

        Collection::macro('trim', function ($character_mask = " \t\n\r\0\x0B") {
            /** @var \Illuminate\Support\Collection */
            $collection = $this;

            return $collection->map(fn ($value) => trim($value, $character_mask));
        });

        Currency::macro('toMoney', function (string|int|float $value, bool $convert = true) {
            /** @var \Akaunting\Money\Currency */
            $currency = $this;

            return new Money(! is_float($value) ? (float) $value : $value, $currency, $convert);
        });
    }

    /**
     * Delete conflicted legacy files.
     */
    protected function deleteConflictedLegacyFiles(): void
    {
        File::delete(config_path('innoclapps.php'));
        File::delete(app_path('Console/Commands/FinalizeUpdateCommand.php'));
        File::delete(app_path('Console/Commands/GenerateJsonLanguageFileCommand.php'));
        File::delete(app_path('Console/Commands/SendScheduledDocuments.php'));
        File::delete(app_path('Console/Commands/ActivitiesNotificationsCommand.php'));
        File::delete(app_path('Console/Commands/UpdateCommand.php'));
        File::delete(config_path('updater.php'));
        File::delete(config_path('settings.php'));
        File::delete(config_path('fields.php'));

        if (is_file(config_path('purifier.php'))) {
            File::delete(config_path('purifier.php'));
        }

        File::delete(config_path('html_purifier.php'));
    }
}
