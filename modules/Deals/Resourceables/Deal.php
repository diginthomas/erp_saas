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

namespace Modules\Deals\Resourceables;

use App\Http\View\FrontendComposers\Template;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Modules\Activities\Fields\NextActivityDate;
use Modules\Activities\Filters\ResourceActivitiesFilter;
use Modules\Billable\Contracts\BillableResource;
use Modules\Billable\Fields\Amount;
use Modules\Billable\Filters\BillableProductsFilter;
use Modules\Billable\Services\BillableService;
use Modules\Comments\Contracts\PipesComments;
use Modules\Contacts\Fields\Companies;
use Modules\Contacts\Fields\Contacts;
use Modules\Core\Actions\DeleteAction;
use Modules\Core\Contracts\Resources\AcceptsCustomFields;
use Modules\Core\Contracts\Resources\Exportable;
use Modules\Core\Contracts\Resources\HasOperations;
use Modules\Core\Contracts\Resources\Importable;
use Modules\Core\Contracts\Resources\Mediable;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Facades\Fields;
use Modules\Core\Facades\Permissions;
use Modules\Core\Fields\Date;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\ID;
use Modules\Core\Fields\RelationshipCount;
use Modules\Core\Fields\Tags;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\User;
use Modules\Core\Filters\Date as DateFilter;
use Modules\Core\Filters\DateTime as DateTimeFilter;
use Modules\Core\Filters\MultiSelect as MultiSelectFilter;
use Modules\Core\Filters\Numeric as NumericFilter;
use Modules\Core\Filters\Select as SelectFilter;
use Modules\Core\Filters\Tags as TagsFilter;
use Modules\Core\Filters\Text as TextFilter;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Menu\MenuItem;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Import\Import;
use Modules\Core\Resource\Resource;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Support\Date\Carbon;
use Modules\Core\Table\Column;
use Modules\Core\Table\Table;
use Modules\Deals\Criteria\ViewAuthorizedDealsCriteria;
use Modules\Deals\Enums\DealStatus;
use Modules\Deals\Events\DealMovedToStage;
use Modules\Deals\Fields\LostReasonField;
use Modules\Deals\Fields\Pipeline as PipelineField;
use Modules\Deals\Fields\PipelineStage;
use Modules\Deals\Filters\DealStatusFilter;
use Modules\Deals\Http\Resources\DealResource;
use Modules\Deals\Models\Deal as DealModel;
use Modules\Deals\Models\Pipeline;
use Modules\Deals\Models\Stage;
use Modules\Deals\Resourceables\Pages\DetailComponent;
use Modules\Documents\Filters\ResourceDocumentsFilter;
use Modules\MailClient\Filters\ResourceEmailsFilter;
use Modules\Notes\Fields\ImportNote;
use Modules\Users\Filters\ResourceUserTeamFilter;
use Modules\Users\Filters\UserFilter;
use Modules\WebForms\Models\WebForm;

class Deal extends Resource implements AcceptsCustomFields, BillableResource, Exportable, HasOperations, Importable, Mediable, PipesComments, Tableable
{
    /**
     * Indicates whether the resource has Zapier hooks
     */
    public static bool $hasZapierHooks = true;

    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * Indicates whether the resource has detail view.
     */
    public static bool $hasDetailView = true;

    /**
     * Indicates whether the resource is globally searchable
     */
    public static bool $globallySearchable = true;

    /**
     * Indicates whether the resource fields are customizeable
     */
    public static bool $fieldsCustomizable = true;

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Deals\Models\Deal';

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make(static::label(), '/deals', 'Banknotes')
                ->position(5)
                ->inQuickCreate()
                ->keyboardShortcutChar('D'),
        ];
    }

    /**
     * Get the resource relationship name when it's associated
     */
    public function associateableName(): string
    {
        return 'deals';
    }

    /**
     * Get the resource available cards
     */
    public function cards(): array
    {
        return [
            (new \Modules\Deals\Cards\ClosingDeals)->onlyOnDashboard()
                ->floatResourceInDetailMode(static::name())
                ->withUserSelection(function ($instance) {
                    return $instance->authorizedToFilterByUser() ? auth()->id() : false;
                })
                ->help(__('deals::deal.cards.closing_info')),

            (new \Modules\Deals\Cards\DealsByStage)->refreshOnActionExecuted()
                ->help(__('core::app.cards.creation_date_info')),

            (new \Modules\Deals\Cards\DealsLostInStage)->color('danger')
                ->onlyOnDashboard(),

            (new \Modules\Deals\Cards\DealsWonInStage)->color('success')
                ->onlyOnDashboard(),

            (new \Modules\Deals\Cards\WonDealsByDay)->refreshOnActionExecuted()
                ->withUserSelection(function ($instance) {
                    return $instance->authorizedToFilterByUser();
                })->color('success'),

            (new \Modules\Deals\Cards\WonDealsByMonth)->withUserSelection(function ($instance) {
                return $instance->authorizedToFilterByUser();
            })->color('success')->onlyOnDashboard(),

            (new \Modules\Deals\Cards\RecentlyCreatedDeals)->onlyOnDashboard()->floatResourceInDetailMode(static::name()),

            (new \Modules\Deals\Cards\RecentlyModifiedDeals)->onlyOnDashboard()->floatResourceInDetailMode(static::name()),

            (new \Modules\Deals\Cards\WonDealsRevenueByMonth)->color('success')
                ->canSeeWhen('is-super-admin')
                ->onlyOnDashboard(),

            (new \Modules\Deals\Cards\CreatedDealsBySaleAgent)->canSee(function ($request) {
                return $request->user()?->canAny(['view all deals', 'view team deals']);
            })
                ->onlyOnDashboard(),

            (new \Modules\Deals\Cards\AssignedDealsBySaleAgent)->canSee(function ($request) {
                return $request->user()?->canAny(['view all deals', 'view team deals']);
            })
                ->onlyOnDashboard(),
        ];
    }

    /**
     * Provide the resource table class instance.
     */
    public function table(Builder $query, ResourceRequest $request): Table
    {
        return new DealTable($query, $request);
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return DealResource::class;
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): string
    {
        return ViewAuthorizedDealsCriteria::class;
    }

    /**
     * Provides the resource available CRUD fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            ID::make()->hidden(),

            Text::make('name', __('deals::fields.deals.name'))
                ->primary()
                ->tapIndexColumn(fn (Column $column) => $column
                    ->width('300px')->minWidth('200px')
                    ->primary()
                    ->route(! $column->isForTrashedTable() ? ['name' => 'view-deal', 'params' => ['id' => '{id}']] : '')
                )
                ->creationRules(['required', 'string'])
                ->updateRules(['filled', 'string'])
                ->importRules('required')
                ->rules('max:191')
                ->hideFromDetail()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->required(true),

            $pipeline = PipelineField::make()->primary()
                ->rules('filled')
                ->required(true)
                ->hideFromDetail()
                ->hideWhenUpdating()
                ->hideFromIndex()
                ->excludeFromImport()
                ->excludeFromSettings()
                ->showValueWhenUnauthorizedToView()
                ->tapIndexColumn(fn (Column $column) => $column->queryWhenHidden()) // index inline edit of stage
                ->inlineEditWith([
                    $inlinePipeline = PipelineField::make()->required(),
                    PipelineStage::make()->dependsOn($inlinePipeline, 'stages'),
                ]),

            PipelineStage::make()->primary()
                ->dependsOn($pipeline, 'stages')
                ->hideFromDetail()
                ->hideWhenUpdating()
                ->excludeFromSettings()
                ->inlineEditWith([$pipeline, PipelineStage::make()->dependsOn($pipeline, 'stages')])
                ->showValueWhenUnauthorizedToView(),

            Amount::make('amount', __('deals::fields.deals.amount'))
                ->readOnly(fn () => $this->resource?->hasProducts() ?? false)
                ->primary()
                ->currency(),

            Date::make('expected_close_date', __('deals::fields.deals.expected_close_date'))
                ->primary()
                ->clearable()
                ->withDefaultValue(Carbon::parse()->endOfMonth()->format('Y-m-d')),

            Tags::make()
                ->forType(DealModel::TAGS_TYPE)
                ->rules(['sometimes', 'nullable', 'array'])
                ->hideFromDetail()
                ->hideFromIndex()
                ->excludeFromSettings(Fields::DETAIL_VIEW),

            Text::make('status', __('deals::deal.status.status'))
                ->excludeFromSettings()
                ->hideFromDetail()
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->excludeFromImport()
                ->fillUsing(function (Model $model, string $attribute, ResourceRequest $request, mixed $value, string $requestAttribute) {
                    $status = DealStatus::find($value);

                    abort_if(
                        $model->isStatusLocked($status),
                        409,
                        'The deal first must be marked as open in order to apply the "'.$status->name.'" status.'
                    );

                    $model->fillStatus($status, $request->lost_reason);
                })
                ->rules(['sometimes', 'nullable', 'string', Rule::in(DealStatus::names())])
                ->showValueWhenUnauthorizedToView()
                ->resolveUsing(fn ($model) => $model->status->name)
                ->displayUsing(fn ($model, $value) => DealStatus::find($value)->label()) // For mail placeholder
                ->tapIndexColumn(function (Column $column) {
                    $column->centered()
                        ->withMeta([
                            'statuses' => collect(DealStatus::cases())->mapWithKeys(function ($status) {
                                return [$status->value => [
                                    'name' => $status->name,
                                    'badge' => $status->badgeVariant(),
                                ]];
                            }),
                        ])
                        ->orderByUsing(function (Builder $query, string $direction) {
                            return $query->orderByRaw('CASE
                                WHEN status ="'.DealStatus::open->value.'" THEN 1
                                WHEN status ="'.DealStatus::lost->value.'" THEN 2
                                WHEN status ="'.DealStatus::won->value.'" THEN 3
                            END '.$direction);
                        });
                }),

            LostReasonField::make('lost_reason', __('deals::deal.lost_reasons.lost_reason'))
                ->hidden()
                ->excludeFromSettings()
                ->excludeFromImportSample()
                ->disableInlineEdit()
                ->rules(array_filter([
                    Rule::excludeIf(fn () => $request->resourceId()
                         && $request->record()->isLost() &&
                         $request->missing('lost_reason')
                    ),
                    (bool) settings('lost_reason_is_required') ? 'required_if:status,lost' : null,
                    'nullable',
                    'string',
                    'max:191',
                ])),

            User::make(__('deals::fields.deals.user.name'))
                ->primary()
                ->acceptLabelAsValue(false)
                ->withMeta(['attributes' => ['placeholder' => __('core::app.no_owner')]])
                ->notification(\Modules\Deals\Notifications\UserAssignedToDeal::class)
                ->trackChangeDate('owner_assigned_date')
                ->hideFromDetail()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->showValueWhenUnauthorizedToView(),

            Contacts::make()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->hideFromDetail()
                ->hideFromIndex()
                ->order(1001),

            Companies::make()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->hideFromDetail()
                ->hideFromIndex()
                ->order(1002),

            DateTime::make('owner_assigned_date', __('deals::fields.deals.owner_assigned_date'))
                ->exceptOnForms()
                ->excludeFromSettings()
                ->hidden(),

            RelationshipCount::make('contacts', __('contacts::contact.total'))
                ->hidden(),

            RelationshipCount::make('companies', __('contacts::company.total'))
                ->hidden(),

            RelationshipCount::make('unreadEmailsForUser', __('mailclient::inbox.unread_count'))
                ->hidden()
                ->authRequired()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('incompleteActivitiesForUser', __('activities::activity.incomplete_activities'))
                ->hidden()
                ->authRequired()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('documents', __('documents::document.total_documents'))
                ->hidden()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('draftDocuments', __('documents::document.total_draft_documents'))
                ->hidden()
                ->excludeFromZapierResponse(),

            RelationshipCount::make('calls', __('calls::call.total_calls'))
                ->hidden(),

            NextActivityDate::make(),

            ImportNote::make(),

            DateTime::make('updated_at', __('core::app.updated_at'))
                ->excludeFromImportSample()
                ->onlyOnIndex()
                ->hidden(),

            DateTime::make('created_at', __('core::app.created_at'))
                ->excludeFromImportSample()
                ->onlyOnIndex()
                ->hidden(),
        ];
    }

    /**
     * Get the resource importable class
     */
    public function importable(): Import
    {
        return new DealImport($this);
    }

    /**
     * Get the resource available filters
     */
    public function filters(ResourceRequest $request): array
    {
        return [
            TextFilter::make('name', __('deals::fields.deals.name'))->withoutNullOperators(),
            NumericFilter::make('amount', __('deals::fields.deals.amount')),
            DateFilter::make('expected_close_date', __('deals::fields.deals.expected_close_date')),

            SelectFilter::make('pipeline_id', __('deals::fields.deals.pipeline.name'))
                ->labelKey('name')
                ->valueKey('id')
                ->options(function () {
                    return Pipeline::select(['id', 'name'])
                        ->visible()
                        ->userOrdered()
                        ->get()
                        ->map(fn (Pipeline $pipeline) => [
                            'id' => $pipeline->id,
                            'name' => $pipeline->name,
                        ]);
                }),

            MultiSelectFilter::make('stage_id', __('deals::fields.deals.stage.name'))
                ->labelKey('name')
                ->valueKey('id')
                ->options(function () use ($request) {
                    return Stage::allStagesForOptions($request->user());
                }),

            DateTimeFilter::make('stage_changed_date', __('deals::deal.stage.changed_date')),

            TagsFilter::make('tags', __('core::tags.tags'))->forType(DealModel::TAGS_TYPE),

            DateTimeFilter::make('won_date', __('deals::deal.won_date'))
                ->help(__('deals::deal.status_related_filter_notice', ['status' => DealStatus::won->label()])),

            DateTimeFilter::make('lost_date', __('deals::deal.lost_date'))
                ->help(__('deals::deal.status_related_filter_notice', ['status' => DealStatus::lost->label()])),

            DealStatusFilter::make(),

            TextFilter::make('lost_reason', __('deals::deal.lost_reasons.lost_reason')),

            UserFilter::make(__('deals::fields.deals.user.name')),
            ResourceUserTeamFilter::make(__('users::team.owner_team')),
            DateTimeFilter::make('owner_assigned_date', __('deals::fields.deals.owner_assigned_date')),
            ResourceDocumentsFilter::make(),
            BillableProductsFilter::make(),
            ResourceActivitiesFilter::make(),
            ResourceEmailsFilter::make(),

            SelectFilter::make('web_form_id', __('webforms::form.form'))
                ->labelKey('title')
                ->valueKey('id')
                ->options(function () {
                    return WebForm::get(['id', 'title'])->map(fn (WebForm $webForm) => [
                        'id' => $webForm->id,
                        'title' => $webForm->title,
                    ]);
                })
                ->canSeeWhen('is-super-admin'),

            DateTimeFilter::make('next_activity_date', __('activities::activity.next_activity_date')),
            UserFilter::make(__('core::app.created_by'), 'created_by')->withoutNullOperators()->canSeeWhen('view all deals'),
            DateTimeFilter::make('updated_at', __('core::app.updated_at')),
            DateTimeFilter::make('created_at', __('core::app.created_at')),
        ];
    }

    /**
     * Provides the resource available actions
     */
    public function actions(ResourceRequest $request): array
    {
        return [
            (new \Modules\Deals\Actions\MarkAsWon)->withoutConfirmation(),
            new \Modules\Deals\Actions\MarkAsLost,
            (new \Modules\Deals\Actions\MarkAsOpen)->withoutConfirmation(),

            new \Modules\Core\Actions\BulkEditAction($this),

            new \Modules\Deals\Actions\ChangeDealStage,

            (new DeleteAction)->authorizedToRunWhen(function (ActionRequest $request, Model $model, int $total) {
                return $request->user()->can($total > 1 ? 'bulkDelete' : 'delete', $model);
            }),
        ];
    }

    /**
     * Prepare display query.
     */
    public function displayQuery(): Builder
    {
        return parent::displayQuery()->with([
            'pipeline.stages',
            'media',
            'contacts.phones', // phones are for calling
            'companies.phones', // phones are for calling
        ]);
    }

    /**
     * Prepare global search query.
     */
    public function globalSearchQuery(ResourceRequest $request): Builder
    {
        return parent::globalSearchQuery($request)->select(['id', 'name', 'created_at']);
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return __('deals::deal.deals');
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return __('deals::deal.deal');
    }

    /**
     * Register permissions for the resource
     */
    public function registerPermissions(): void
    {
        $this->registerCommonPermissions();

        Permissions::register(function ($manager) {
            $manager->group($this->name(), function ($manager) {
                $manager->view('export', [
                    'permissions' => [
                        'export deals' => __('core::app.export.export'),
                    ],
                ]);
            });
        });
    }

    /**
     * Register the settings menu items for the resource
     */
    public function settingsMenu(): array
    {
        return [
            SettingsMenuItem::make(__('deals::deal.deals'), '/settings/deals', 'Folder')->order(22),
        ];
    }

    /**
     * Get the resource frontend template
     */
    public function frontendTemplate(): Template
    {
        return (new Template)->detailComponent(new DetailComponent);
    }

    /**
     * Handle the "afterCreate" resource record hook.
     */
    public function afterCreate(Model $model, ResourceRequest $request): void
    {
        // We will check if the provided billable has products, if yes, then in this case the user
        // wants to add products however, if no, we won't save the billable as it will update the
        // amount column of the deal to 0 but the user may have entered an amount for this deal when creating
        if (count($request->billable['products'] ?? []) > 0) {
            (new BillableService)->save($request->billable, $model);
        }
    }

    /**
     * Handle the "beforeUpdate" resource record hook.
     */
    public function beforeUpdate(Model $model, ResourceRequest $request): void
    {
        if ($model->isDirty('stage_id')) {
            $request->merge(['_original_stage' => $model->getOriginal('stage_id')]);
        }
    }

    /**
     * Handle the "afterUpdate" resource record hook.
     */
    public function afterUpdate(Model $model, ResourceRequest $request): void
    {
        if ($request->billable) {
            (new BillableService)->save($request->billable, $model);
        }

        if ($model->wasChanged('stage_id')) {
            DealMovedToStage::dispatch(
                $model,
                Stage::findFromObjectCache($request->input('_original_stage'))
            );
        }
    }

    /**
     * Serialize the resource
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'pages' => $this->frontendTemplate(),
        ]);
    }
}
