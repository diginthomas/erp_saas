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

namespace Modules\Activities\Observers;

use Modules\Activities\Models\Activity;
use Modules\Activities\Models\ActivityType;
use Modules\Users\Models\User;

class ActivityObserver
{
    /**
     * Handle the Activity "creating" event.
     */
    public function creating(Activity $activity): void
    {
        if (! $activity->end_date) {
            $activity->end_date = $activity->due_date;
        }

        if (! $activity->activity_type_id) {
            $activity->activity_type_id = ActivityType::getDefaultType();
        }

        $activity->reminder_at = Activity::determineReminderAtDate($activity);
    }

    /**
     * Handle the Activity "updating" event.
     */
    public function updating(Activity $activity): void
    {
        $activity::$preUpdateUser = $activity->isDirty('user_id') ?
            User::find($activity->getOriginal('user_id')) :
            User::find($activity->user_id);

        // We will update the date only if the attribute is set on the model
        // because if it's not set, probably was not provided and no need to determine or update the reminder_at
        if (array_key_exists('reminder_minutes_before', $activity->getAttributes())) {
            tap($activity->reminder_at, function ($originalReminder) use (&$activity) {
                $activity->reminder_at = Activity::determineReminderAtDate($activity);

                // We will check if the reminder_at column has been changed, if yes,
                // we will reset the reminded_at value to null so new reminder can be sent to the user
                if (is_null($activity->reminder_at) || ($activity->is_reminded && $originalReminder->ne($activity->reminder_at))) {
                    $activity->reminded_at = null;
                }
            });
        }
    }

    /**
     * Handle the Activity "deleting" event.
     */
    public function deleting(Activity $activity): void
    {
        if ($activity->isForceDeleting()) {
            $activity->purge(false);
        }
    }
}
