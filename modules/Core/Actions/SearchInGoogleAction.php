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

namespace Modules\Core\Actions;

use Illuminate\Support\Collection;
use Modules\Core\Http\Requests\ActionRequest;

class SearchInGoogleAction extends Action
{
    /**
     * Indicates that this action is without confirmation dialog.
     */
    public bool $withoutConfirmation = true;

    /**
     * Indicates that the action will be hidden on the index view.
     */
    public bool $hideOnIndex = true;

    /**
     * Handle method.
     */
    public function handle(Collection $models, ActionFields $fields): array
    {
        return static::openInNewTab('https://www.google.com/search?q='.urlencode($models->first()->display_name));
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
        return __('core::actions.search_in_google');
    }
}
