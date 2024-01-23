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

namespace Modules\Activities\Actions;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Modules\Core\Actions\Action;
use Modules\Core\Actions\ActionFields;
use Modules\Core\Http\Requests\ActionRequest;

class DownloadIcsFile extends Action
{
    /**
     * Indicates that the action does not have confirmation dialog.
     */
    public bool $withoutConfirmation = true;

    /**
     * The XHR response type that should be passed from the front-end.
     */
    public string $responseType = 'blob';

    /**
     * Handle method.
     */
    public function handle(Collection $models, ActionFields $fields): Response
    {
        if ($models->count() > 1) {
            return static::error('Please run this on only one activity.');
        }

        $activity = $models->first();

        return response($activity->generateICSInstance()->get(), 200, [
            'Content-Type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename='.$activity->icsFilename().'.ics',
            'charset' => 'utf-8',
        ]);
    }

    /**
     * @param  \Illumindate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        return $request->user()->can('view', $model);
    }

    /**
     * Action name.
     */
    public function name(): string
    {
        return __('activities::activity.download_ics');
    }
}
