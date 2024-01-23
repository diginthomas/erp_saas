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

namespace App\Http\Controllers\Api;

use Exception;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\MailableTemplates;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Updater\Migration;
use Modules\Translator\Translator;

class SystemToolsController extends ApiController
{
    /**
     * Generate i18n file.
     */
    public function i18n(): void
    {
        // i18n tool flag

        Translator::generateJsonLanguageFile();
    }

    /**
     * Clear application cache.
     */
    public function clearCache(): void
    {
        // Clear cache tool flag

        Innoclapps::clearCache();
        Innoclapps::restartQueue();
    }

    /**
     * Create application storage link.
     */
    public function storageLink(): void
    {
        // Storage link tool flag

        try {
            Innoclapps::createStorageLink();
        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    /**
     * Run the database migrations.
     */
    public function migrate(): void
    {
        // Migrate tool flag

        Migration::migrate();
    }

    /**
     * Cache the application bootstrap files.
     */
    public function optimize(): void
    {
        // Optimize tool flag

        Innoclapps::optimize();
        Innoclapps::restartQueue();
    }

    /**
     * Seed the mailable templates.
     */
    public function seedMailableTemplates(): void
    {
        // Seed mailable templates tool flag

        MailableTemplates::seedIfRequired();
    }
}
