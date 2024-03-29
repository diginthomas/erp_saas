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

namespace Modules\Core\Http\Controllers\Api;

use App\Installer\RequirementsChecker;
use Illuminate\Http\JsonResponse;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Updater\Updater;

class UpdateController extends ApiController
{
    /**
     * Get information about update.
     */
    public function index(Updater $updater): JsonResponse
    {
        return $this->response([
            'installed_version' => $updater->getVersionInstalled(),
            'latest_available_version' => $updater->getVersionAvailable(),
            'is_new_version_available' => $updater->isNewVersionAvailable(),
            'purchase_key' => $updater->getPurchaseKey(),
        ]);
    }

    /**
     * Perform an application update.
     */
    public function update(?string $purchaseKey = null): JsonResponse
    {
        // Update flag
        $requirements = new RequirementsChecker();

        abort_if($requirements->fails('zip'), 409, __('core::update.update_zip_is_required'));

        // Save the purchase key for future usage
        if ($purchaseKey) {
            settings(['purchase_key' => $purchaseKey]);
        }

        Innoclapps::runCommand('updater:update', [
            '--key' => $purchaseKey,
            '--force' => true,
        ]);

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
