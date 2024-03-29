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

namespace Modules\MailClient\Providers;

use App\Http\View\FrontendComposers\Tab;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Modules\Contacts\Resourceables\Company\Pages\DetailComponent as CompanyDetailComponent;
use Modules\Contacts\Resourceables\Contact\Pages\DetailComponent as ContactDetailComponent;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\Menu;
use Modules\Core\Facades\Permissions;
use Modules\Core\Fields\Email;
use Modules\Core\Menu\MenuItem;
use Modules\Core\Support\OAuth\Events\OAuthAccountConnected;
use Modules\Core\Support\OAuth\Events\OAuthAccountDeleting;
use Modules\Core\SystemInfo;
use Modules\Deals\Resourceables\Pages\DetailComponent as DealDetailComponent;
use Modules\MailClient\Client\ClientManager;
use Modules\MailClient\Client\ConnectionType;
use Modules\MailClient\Client\FolderType;
use Modules\MailClient\Console\Commands\PruneStaleScheduledEmails;
use Modules\MailClient\Console\Commands\SendScheduledEmails;
use Modules\MailClient\Console\Commands\SyncEmailAccounts;
use Modules\MailClient\Criteria\EmailAccountsForUserCriteria;
use Modules\MailClient\Listeners\CreateEmailAccountViaOAuth;
use Modules\MailClient\Listeners\StopRelatedOAuthEmailAccounts;
use Modules\MailClient\Listeners\TransferMailClientUserData;
use Modules\MailClient\Models\EmailAccount;
use Modules\MailClient\Models\EmailAccountMessage;
use Modules\Users\Events\TransferringUserData;

class MailClientServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'MailClient';

    protected string $moduleNameLower = 'mailclient';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        $this->registerPermissions();

        Email::useDetailComponent('detail-email-sendable-field');
        Email::useIndexComponent('index-email-sendable-field');

        $this->app['events']->listen(OAuthAccountConnected::class, CreateEmailAccountViaOAuth::class);
        $this->app['events']->listen(OAuthAccountDeleting::class, StopRelatedOAuthEmailAccounts::class);
        $this->app['events']->listen(TransferringUserData::class, TransferMailClientUserData::class);

        $this->commands([
            SyncEmailAccounts::class,
            SendScheduledEmails::class,
            PruneStaleScheduledEmails::class,
        ]);

        SystemInfo::register('MAIL_CLIENT_SYNC_INTERVAL', $this->app['config']->get('mailclient.sync.interval'));

        $this->registerRelatedRecordsDetailTab();

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
     * Boot the mail client module.
     */
    protected function bootModule(): void
    {
        $this->registerResources();

        Innoclapps::whenReadyForServing(function () {
            Innoclapps::booted($this->registerMenuItems(...));
            Innoclapps::booted($this->shareDataToScript(...));
            $this->scheduleTasks();
        });
    }

    /**
     * Schedule the document related tasks.
     */
    public function scheduleTasks(): void
    {
        /** @var \Illuminate\Console\Scheduling\Schedule */
        $schedule = $this->app->make(Schedule::class);

        $syncOutputPath = storage_path('logs/email-accounts-sync.log');
        $syncCommandCronExpression = config('mailclient.sync.interval');

        $syncCommandName = 'sync-email-accounts';
        $pruneFailedScheduledEmailsName = 'prune-failed-scheduled-emails';
        $sendScheduledEmailsName = 'send-scheduled-emails';

        if (Innoclapps::canRunProcess()) {
            $schedule->command('mailclient:sync', ['--broadcast', '--isolated' => 5])
                ->cron($syncCommandCronExpression)
                ->name($syncCommandName)
                ->withoutOverlapping(30)
                ->sendOutputTo($syncOutputPath);

            $schedule->command('mailclient:prune-failed')
                ->daily()
                ->name($pruneFailedScheduledEmailsName);

            $schedule->command('mailclient:send-scheduled')
                ->everyThreeMinutes()
                ->name($sendScheduledEmailsName);
        } else {
            $schedule
                ->call(
                    fn () => Artisan::call('mailclient:sync', ['--broadcast' => true, '--isolated' => 5])
                )
                ->cron($syncCommandCronExpression)
                ->name($syncCommandName)
                ->withoutOverlapping(30)
                ->sendOutputTo($syncOutputPath);

            $schedule
                ->call(fn () => Artisan::call('mailclient:prune-failed'))
                ->daily()
                ->name($pruneFailedScheduledEmailsName);

            $schedule
                ->call(fn () => Artisan::call('mailclient:send-scheduled'))
                ->everyThreeMinutes()
                ->name($sendScheduledEmailsName);
        }
    }

    /**
     * Register the mail client module resources.
     */
    public function registerResources(): void
    {
        Innoclapps::resources([
            \Modules\MailClient\Resourceables\EmailMessage::class,
        ]);
    }

    /**
     * Share data to script.
     */
    protected function shareDataToScript(): void
    {
        Innoclapps::provideToScript(['mail' => [
            'tags_type' => EmailAccountMessage::TAGS_TYPE,
            'reply_prefix' => config('mailclient.reply_prefix'),
            'forward_prefix' => config('mailclient.forward_prefix'),
            'accounts' => [
                'connections' => ConnectionType::cases(),
                'encryptions' => ClientManager::ENCRYPTION_TYPES,
                'from_name' => EmailAccount::DEFAULT_FROM_NAME_HEADER,
            ],
            'folders' => [
                'outgoing' => FolderType::outgoingTypes(),
                'incoming' => FolderType::incomingTypes(),
                'other' => FolderType::OTHER,
                'drafts' => FolderType::DRAFTS,
            ],
        ],
        ]);
    }

    /**
     * Register the menu items.
     */
    private function registerMenuItems(): void
    {
        $accounts = auth()->check() ? EmailAccount::with('oAuthAccount')
            ->criteria(EmailAccountsForUserCriteria::class)
            ->get()->filter->canSendEmail() : null;

        Menu::register(
            MenuItem::make(__('mailclient::inbox.inbox'), '/inbox', 'Inbox')
                ->position(15)
                ->badge(fn () => EmailAccount::countUnreadMessagesForUser(Auth::user()))
                ->inQuickCreate(! is_null($accounts?->filter->isPrimary()->first() ?? $accounts?->first()))
                ->quickCreateName(__('mailclient::mail.send'))
                ->quickCreateRoute('/inbox?compose=true')
                ->keyboardShortcutChar('E')
                ->badgeVariant('info')
        );
    }

    /**
     * Register the mail client module permissions.
     */
    protected function registerPermissions(): void
    {
        Permissions::register(function ($manager) {
            $manager->group(['name' => 'inbox', 'as' => __('mailclient::inbox.shared')], function ($manager) {
                $manager->view('access-inbox', [
                    'as' => __('core::role.capabilities.access'),
                    'permissions' => [
                        'access shared inbox' => __('core::role.capabilities.access'),
                    ],
                ]);
            });
        });
    }

    /**
     * Register the documents module related tabs.
     */
    public function registerRelatedRecordsDetailTab(): void
    {
        $tab = Tab::make('emails', 'emails-tab')->panel('emails-tab-panel')->order(20);

        ContactDetailComponent::registerTab($tab);
        CompanyDetailComponent::registerTab($tab);
        DealDetailComponent::registerTab($tab);
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
