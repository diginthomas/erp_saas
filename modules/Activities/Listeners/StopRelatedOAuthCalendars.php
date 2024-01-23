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

namespace Modules\Activities\Listeners;

use Modules\Activities\Models\Calendar;
use Modules\Core\Support\OAuth\Events\OAuthAccountDeleting;

class StopRelatedOAuthCalendars
{
    /**
     * Stop the related calendars of the OAuth account when deleting.
     */
    public function handle(OAuthAccountDeleting $event): void
    {
        Calendar::with('synchronization')
            ->where('access_token_id', $event->account->id)
            ->get()
            ->each(function (Calendar $calendar) {
                $calendar->forceFill(['access_token_id' => null])->save();
                $calendar->disableSync();
            });
    }
}
