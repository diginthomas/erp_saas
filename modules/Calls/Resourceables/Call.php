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

namespace Modules\Calls\Resourceables;

use Modules\Calls\Http\Resources\CallOutcomeResource;
use Modules\Calls\Http\Resources\CallResource;
use Modules\Calls\Models\CallOutcome;
use Modules\Comments\Contracts\HasComments;
use Modules\Core\Contracts\Resources\HasOperations;
use Modules\Core\Criteria\RelatedCriteria;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\BelongsTo;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\Editor;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Resource\Resource;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Support\Date\Carbon;

class Call extends Resource implements HasComments, HasOperations
{
    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Calls\Models\Call';

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return CallResource::class;
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): ?string
    {
        if (! auth()->user()->isSuperAdmin()) {
            return RelatedCriteria::class;
        }

        return null;
    }

    /**
     * Set the available resource fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            BelongsTo::make('outcome', CallOutcome::class, __('calls::call.outcome.outcome'))
                ->rules(['required', 'numeric'])
                ->setJsonResource(CallOutcomeResource::class)
                ->showValueWhenUnauthorizedToView() // when viewing related record e.q. deal
                ->options(Innoclapps::resourceByModel(CallOutcome::class))
                ->width('half')
                ->withMeta([
                    'attributes' => [
                        'clearable' => false,
                        'placeholder' => __('calls::call.outcome.select_outcome'),
                    ],
                ]),

            DateTime::make('date', __('calls::call.date'))
                ->withDefaultValue(Carbon::parse())
                ->width('half')
                ->rules('required'),

            Editor::make('body')
                ->rules(['required', 'string'])
                ->validationMessages(['required' => __('validation.required_without_label')])
                ->withMentions()
                ->withMeta([
                    'attributes' => [
                        'placeholder' => __('calls::call.log'),
                    ],
                ]),
        ];
    }

    /**
     * Get the resource available cards
     */
    public function cards(): array
    {
        return [
            (new \Modules\Calls\Cards\LoggedCallsByDay)->withUserSelection()->canSeeWhen('is-super-admin'),
            (new \Modules\Calls\Cards\TotalLoggedCallsBySaleAgent)->canSeeWhen('is-super-admin')->color('success'),
            (new \Modules\Calls\Cards\LoggedCalls)->canSeeWhen('is-super-admin')->withUserSelection(),
            (new \Modules\Calls\Cards\OverviewByCallOutcome)->color('info')->withUserSelection(function () {
                return auth()->user()->isSuperAdmin();
            }),
        ];
    }

    /**
     * Get the resource relationship name when it's associated
     */
    public function associateableName(): string
    {
        return 'calls';
    }

    /**
     * Get the resource rules available for create and update
     */
    public function rules(ResourceRequest $request): array
    {
        return [
            'via_resource' => ['required', 'in:contacts,companies,deals', 'string'],
            'via_resource_id' => ['required', 'numeric'],
        ];
    }

    /**
     * Register the settings menu items for the resource
     */
    public function settingsMenu(): array
    {
        return [
            SettingsMenuItem::make(__('calls::call.calls'), '/settings/calls', 'DeviceMobile')->order(25),
        ];
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return __('calls::call.calls');
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return __('calls::call.call');
    }
}
