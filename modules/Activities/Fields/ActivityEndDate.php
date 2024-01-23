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

namespace Modules\Activities\Fields;

use Closure;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Support\Date\Carbon;

class ActivityEndDate extends ActivityDueDate
{
    /**
     * The model attribute that holds the time
     */
    protected string $dateField = 'end_date';

    /**
     * The model attribute that holds the date
     */
    protected string $timeField = 'end_time';

    /**
     * Field component.
     */
    public static $component = 'activity-end-date-field';

    /**
     * Initialize new ActivityEndDate instance
     *
     * @param  string  $label
     */
    public function __construct($label)
    {
        parent::__construct($label);

        $this->resolveForJsonResourceUsing(function (Model $model, string $attribute) {
            return [
                $attribute => $model->end_time ? Carbon::parse($model->fullEndDate)->toJSON() : $model->end_date,
            ];
        })->rules(function (string $attribute, mixed $value, Closure $fail, ResourceRequest $request) {
            if (empty($value)) {
                return;
            }

            $dueDate = $request->input('due_date');
            $endDate = $request->input('end_date');
            $hasEndTime = $this->hasTime($endDate);
            $hasDueTime = $this->hasTime($dueDate);

            $dueCarbon = Carbon::parse($dueDate);
            $endCarbon = Carbon::parse($endDate);

            // When all day or both due_date and due_time has time, we will
            // compare the dates directly as it's approriate
            if ((! $hasEndTime && ! $hasDueTime) || ($hasEndTime && $hasDueTime)) {
                if ($endCarbon->lessThan($dueCarbon)) {
                    $fail('activities::activity.validation.end_date.less_than_due')->translate();
                }
            } elseif (! $hasEndTime && $hasDueTime) {
                // Because we cannot compare date with no time with date with time, we will
                // just add the same hour and minute on the end date from the due date to
                // perform the comparision, this will make sure that proper validation and comparision
                // Regular date e.q. 2021-12-14 (in this case it's in user timezone)
                $dueCarbonInUserTimezone = Carbon::inUserTimezone($dueCarbon);

                // Convert it to UTC/App timezone to perform comparision
                $endCarbon = Carbon::asCurrentTimezone($endCarbon->format('Y-m-d H:i:s'))
                    ->hour($dueCarbonInUserTimezone->hour)
                    ->minute($dueCarbonInUserTimezone->minute)
                    ->second($dueCarbonInUserTimezone->second)
                    ->inAppTimezone();

                if ($endCarbon->lessThan($dueCarbon)) {
                    $fail('activities::activity.validation.end_date.less_than_due')->translate();
                }

                if (! $endCarbon->isSameDay($dueCarbon)) {
                    $fail('activities::activity.validation.end_time.required_when_end_date_is_in_future')->translate();
                }
            }
        });
    }
}
