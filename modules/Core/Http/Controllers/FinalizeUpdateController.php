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
use Modules\Core\Updater\UpdateFinalizer;

class FinalizeUpdateController extends Controller
{
    /**
     * Show the update finalization action.
     */
    public function show(UpdateFinalizer $finalizer): View
    {
        abort_unless($finalizer->needed(), 404);

        return view('core::update.finalize');
    }

    /**
     * Perform update finalization.
     */
    public function finalize(UpdateFinalizer $finalizer): void
    {
        abort_unless($finalizer->needed(), 404);

        $finalizer->run();
    }
}
