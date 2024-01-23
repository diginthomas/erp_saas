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

namespace Modules\MailClient\Http\Controllers\Api;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Criteria\RequestCriteria;
use Modules\Core\Http\Controllers\ApiController;
use Modules\MailClient\Criteria\EmailAccountsForUserCriteria;
use Modules\MailClient\Http\Resources\ScheduledEmailResource;
use Modules\MailClient\Models\ScheduledEmail;

class ScheduledEmailController extends ApiController
{
    /**
     * Display a listing of the scheduled emails.
     */
    public function index(Request $request): JsonResponse
    {
        $emails = ScheduledEmail::query()
            ->withWhereHas(
                'account', fn ($query) => $query->criteria(EmailAccountsForUserCriteria::class)
            )
            ->when($request->has(['via_resource', 'via_resource_id']), function (Builder $query) use ($request) {
                $query->ofResource($request->via_resource, $request->integer('via_resource_id'));
            })
            ->with('user')
            ->orderBy('scheduled_at')
            ->criteria(RequestCriteria::class)
            ->paginate($request->integer('per_page') ?: null);

        return $this->response(
            ScheduledEmailResource::collection($emails)
        );
    }

    /**
     * Remove the specified scheduled email from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $message = ScheduledEmail::withWhereHas(
            'account', fn ($query) => $query->criteria(EmailAccountsForUserCriteria::class)
        )->findOrFail($id);

        $this->authorize('delete', $message);

        if ($message->isSending()) {
            abort(409, 'Cannot delete scheduled email that is in the process of sending.');
        }

        $message->delete();

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
