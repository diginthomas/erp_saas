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

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Updater\Migration;

class MigrateController extends Controller
{
    /**
     * Show the migration required action.
     */
    public function show(): View
    {
        abort_unless(Innoclapps::requiresMigration(), 404);

        return view('core::migrate');
    }

    /**
     * Perform migration.
     */
    public function migrate(): void
    {
        abort_unless(Innoclapps::requiresMigration(), 404);

        Migration::migrate();
    }
}
