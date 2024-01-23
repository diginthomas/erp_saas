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

namespace Modules\Core\Http\Controllers\Api\Resource;

use Illuminate\Http\JsonResponse;
use Modules\Core\Contracts\Resources\HasEmail;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Resource\EmailSearch;

class EmailSearchController extends ApiController
{
    /**
     * Perform email search.
     */
    public function handle(ResourceRequest $request): JsonResponse
    {
        if (empty($request->q)) {
            return $this->response([]);
        }

        $resources = Innoclapps::registeredResources()->whereInstanceOf(HasEmail::class);

        return $this->response(
            new EmailSearch($request, $resources->all())
        );
    }
}
