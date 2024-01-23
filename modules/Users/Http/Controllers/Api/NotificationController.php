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

namespace Modules\Users\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Core\Http\Controllers\ApiController;

class NotificationController extends ApiController
{
    /**
     * List current user notifications.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->response(
            $request->user()->notifications()->paginate($request->integer('per_page', 15))
        );
    }

    /**
     * Retrieve current user notification.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        return $this->response(
            $request->user()->notifications()->findOrFail($id)
        );
    }

    /**
     * Set all notifications for current user as read.
     */
    public function update(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return $this->response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete current user notification
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $request->user()
            ->notifications()
            ->findOrFail($id)
            ->delete();

        return $this->response('', Response::HTTP_NO_CONTENT);
    }
}
