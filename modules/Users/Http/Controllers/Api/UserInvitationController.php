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
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Users\Mail\InvitationCreated;
use Modules\Users\Models\User;
use Modules\Users\Models\UserInvitation;

class UserInvitationController extends ApiController
{
    /**
     * Invite the user to create account.
     */
    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'emails' => 'required|array',
            'emails.*' => ['email', Rule::unique((new User())->getTable(), 'email')],
            'super_admin' => 'nullable|boolean',
            'access_api' => 'nullable|boolean',
            'roles' => 'nullable|array',
            'teams' => 'nullable|array',
        ], [], ['emails.*' => __('users::user.email')]);

        foreach ($data['emails'] as $email) {
            $this->invite(array_merge($data, ['email' => $email]), $request);
        }

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Perform invitation.
     */
    protected function invite(array $attributes, Request $request): void
    {
        UserInvitation::where('email', $attributes['email'])->first()?->delete();

        $invitation = new UserInvitation([
            'email' => $attributes['email'],
            'super_admin' => $attributes['super_admin'] ?? false,
            'access_api' => $attributes['access_api'] ?? false,
            'roles' => $attributes['roles'] ?? null,
            'teams' => $attributes['teams'] ?? null,
        ]);

        $invitation->save();

        Mail::to($invitation->email)
            ->locale($request->user()->preferredLocale())
            ->send(new InvitationCreated($invitation));
    }
}
