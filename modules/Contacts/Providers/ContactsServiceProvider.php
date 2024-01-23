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

namespace Modules\Contacts\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Contacts\Listeners\AttachEmailAccountMessageToContact;
use Modules\Contacts\Listeners\CreateContactFromEmailAccountMessage;
use Modules\Contacts\Listeners\TransferContactsUserData;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Observers\CompanyObserver;
use Modules\Contacts\Observers\ContactObserver;
use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\MailableTemplates;
use Modules\Core\Notifications\Notifications;
use Modules\Core\Settings\DefaultSettings;
use Modules\Core\Workflow\Workflows;
use Modules\MailClient\Events\EmailAccountMessageCreated;
use Modules\Users\Events\TransferringUserData;

class ContactsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Contacts';

    protected string $moduleNameLower = 'contacts';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        DatabaseState::register([
            \Modules\Contacts\Database\State\EnsureDefaultFiltersArePresent::class,
            \Modules\Contacts\Database\State\EnsureIndustriesArePresent::class,
            \Modules\Contacts\Database\State\EnsureSourcesArePresent::class,
            \Modules\Contacts\Database\State\EnsureDefaultContactTagsArePresent::class,
        ]);

        DefaultSettings::add('require_calling_prefix_on_phones', true);
        DefaultSettings::add('auto_associate_company_to_contact', 1);

        $this->app['events']->listen(EmailAccountMessageCreated::class, CreateContactFromEmailAccountMessage::class);
        $this->app['events']->listen(EmailAccountMessageCreated::class, AttachEmailAccountMessageToContact::class);
        $this->app['events']->listen(TransferringUserData::class, TransferContactsUserData::class);

        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

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

        Contact::observe(ContactObserver::class);
        Company::observe(CompanyObserver::class);

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
            \Modules\Contacts\Resourceables\Company\Company::class,
            \Modules\Contacts\Resourceables\Contact\Contact::class,
            \Modules\Contacts\Resourceables\Source::class,
            \Modules\Contacts\Resourceables\Industry::class,
        ]);
    }

    /**
     * Register the documents module available notifications.
     */
    public function registerNotifications(): void
    {
        Notifications::register([
            \Modules\Contacts\Notifications\UserAssignedToCompany::class,
            \Modules\Contacts\Notifications\UserAssignedToContact::class,
        ]);
    }

    /**
     * Register the documents module available mailables.
     */
    public function registerMailables(): void
    {
        MailableTemplates::register([
            \Modules\Contacts\Mail\UserAssignedToCompany::class,
            \Modules\Contacts\Mail\UserAssignedToContact::class,
        ]);
    }

    /**
     * Register the documents module available workflows.
     */
    public function registerWorkflowTriggers(): void
    {
        Workflows::triggers([
            \Modules\Contacts\Workflow\Triggers\CompanyCreated::class,
            \Modules\Contacts\Workflow\Triggers\ContactCreated::class,
        ]);
    }

    /**
     * Share data to script.
     */
    public function shareDataToScript(): void
    {
        Innoclapps::provideToScript([
            'contacts' => [
                'tags_type' => Contact::TAGS_TYPE,
            ],
            'contact_fields_height' => settings('contact_fields_height'),
            'company_fields_height' => settings('company_fields_height'),
        ]);
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
