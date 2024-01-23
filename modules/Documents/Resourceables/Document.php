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

namespace Modules\Documents\Resourceables;

use Illuminate\Database\Eloquent\Builder;
use Modules\Billable\Contracts\BillableResource;
use Modules\Billable\Fields\Amount;
use Modules\Billable\Filters\BillableProductsFilter;
use Modules\Contacts\Fields\Companies;
use Modules\Contacts\Fields\Contacts;
use Modules\Core\Actions\DeleteAction;
use Modules\Core\Contracts\Resources\Cloneable;
use Modules\Core\Contracts\Resources\HasOperations;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Fields\BelongsTo;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\User;
use Modules\Core\Filters\DateTime as DateTimeFilter;
use Modules\Core\Filters\Numeric as NumericFilter;
use Modules\Core\Filters\Text as TextFilter;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Menu\MenuItem;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Events\ResourceRecordCreated;
use Modules\Core\Resource\Events\ResourceRecordUpdated;
use Modules\Core\Resource\Resource;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Table\BelongsToColumn;
use Modules\Core\Table\Column;
use Modules\Core\Table\Table;
use Modules\Deals\Fields\Deals;
use Modules\Documents\Concerns\ValidatesDocument;
use Modules\Documents\Criteria\ViewAuthorizedDocumentsCriteria;
use Modules\Documents\Enums\DocumentStatus;
use Modules\Documents\Filters\DocumentBrandFilter;
use Modules\Documents\Filters\DocumentStatusFilter;
use Modules\Documents\Filters\DocumentTypeFilter;
use Modules\Documents\Http\Resources\DocumentResource;
use Modules\Documents\Models\DocumentType;
use Modules\Documents\Services\DocumentService;
use Modules\Users\Filters\ResourceUserTeamFilter;
use Modules\Users\Filters\UserFilter;

class Document extends Resource implements BillableResource, Cloneable, HasOperations, Tableable
{
    use ValidatesDocument;

    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'title';

    /**
     * Indicates whether the resource is globally searchable
     */
    public static bool $globallySearchable = true;

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Documents\Models\Document';

    /**
     * Get the menu items for the resource
     */
    public function menu(): array
    {
        return [
            MenuItem::make(static::label(), '/documents', 'DocumentText')
                ->position(20)
                ->inQuickCreate()
                ->keyboardShortcutChar('F'),
        ];
    }

    /**
     * Create new resource record in storage.
     */
    public function create(Model $model, ResourceRequest $request): Model
    {
        $document = (new DocumentService)->create($model, $request->all());

        ResourceRecordCreated::dispatch($document, $this);

        return $document;
    }

    /**
     * Update resource record in storage.
     */
    public function update(Model $model, ResourceRequest $request): Model
    {
        $document = (new DocumentService)->update($model, $request->all());

        ResourceRecordUpdated::dispatch($document, $this);

        return $document;
    }

    /**
     * Get the resource relationship name when it's associated
     */
    public function associateableName(): string
    {
        return 'documents';
    }

    /**
     * Get the resource available cards
     */
    public function cards(): array
    {
        return [
            (new \Modules\Documents\Cards\SentDocumentsByDay)->withUserSelection(function ($instance) {
                return $instance->authorizedToFilterByUser();
            })->color('success'),
            (new \Modules\Documents\Cards\DocumentsByType)->onlyOnDashboard()->help(__('core::app.cards.creation_date_info')),
            (new \Modules\Documents\Cards\DocumentsByStatus)->refreshOnActionExecuted()->help(__('core::app.cards.creation_date_info')),
        ];
    }

    /**
     * Provide the resource table class instance.
     */
    public function table(Builder $query, ResourceRequest $request): Table
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return (new Table($query, $request))->customizeable()
            ->appends(['public_url', 'status'])
            ->withActionsColumn()
            ->select([
                'uuid', // for public_url append
                'user_id', // user_id is for the policy checks
                'status', // for showing the dropdown send document item and disable inline edit checks
            ])->orderBy('created_at', 'desc');
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return DocumentResource::class;
    }

    /**
     * Prepare global search query.
     */
    public function globalSearchQuery(ResourceRequest $request): Builder
    {
        return parent::globalSearchQuery($request)->select(['id', 'title', 'created_at']);
    }

    /**
     * Get columns that should be used for global search.
     */
    public function globalSearchColumns(): array
    {
        return ['title' => 'like'];
    }

    /**
     * Get the resource search columns.
     */
    public function searchableColumns(): array
    {
        return [
            'title' => 'like',
            'status',
            'amount',
            'brand_id',
            'document_type_id',
            'sent_by',
            'user_id',
            'created_by',
        ];
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): string
    {
        return ViewAuthorizedDocumentsCriteria::class;
    }

    /**
     * Clone the resource record from the given id
     */
    public function clone(Model $model, int $userId): Model
    {
        return $model->clone($userId);
    }

    /**
     * Resolve the fields for placeholders.
     */
    public function fieldsForPlaceholders(): FieldsCollection
    {
        return $this->fieldsForIndex()->filterForPlaceholders();
    }

    /**
     * Resolve the fields for index.
     */
    public function fieldsForIndex(): FieldsCollection
    {
        return new FieldsCollection([
            Text::make('title', __('documents::fields.documents.title'))
                ->required()
                ->tapIndexColumn(fn (Column $column) => $column
                    ->width('300px')->minWidth('200px')
                    ->primary()
                    ->route(! $column->isForTrashedTable() ? ['name' => 'view-document', 'params' => ['id' => '{id}']] : '')
                )
                ->disableInlineEdit(fn ($model) => $model->status === DocumentStatus::ACCEPTED),

            BelongsTo::make('type', DocumentType::class, __('documents::document.type.type'))
                ->required()
                ->displayAsPills()
                ->tapIndexColumn(fn (BelongsToColumn $column) => $column
                    ->select(['swatch_color'])
                    ->appends('icon')
                    ->width('200px')
                ),

            Text::make('status', __('documents::document.status.status'))
                ->tapIndexColumn(fn (Column $column) => $column->width('200px')->centered())
                ->resolveUsing(fn ($model) => $model->status->value)
                ->disableInlineEdit()
                ->displayUsing(fn ($model, $value) => DocumentStatus::tryFrom($value)->displayName()), // For mail placeholder

            User::make(__('documents::fields.documents.user.name'))
                ->required()
                ->tapIndexColumn(fn (Column $column) => $column->queryWhenHidden()) // policy
                ->disableInlineEdit(fn ($model) => $model->status === DocumentStatus::ACCEPTED),

            Amount::make('amount', __('documents::fields.documents.amount'))
                ->currency()
                ->onlyProducts()
                ->disableInlineEdit(fn ($model) => $model->status === DocumentStatus::ACCEPTED),

            Contacts::make()->hidden(),

            Companies::make()->hidden(),

            Deals::make()->hidden(),

            User::make(__('core::app.created_by'), 'creator', 'created_by')
                ->disableInlineEdit()
                ->hidden(),

            DateTime::make('last_date_sent', __('documents::fields.documents.last_date_sent'))
                ->disableInlineEdit()
                ->hidden(),

            DateTime::make('original_date_sent', __('documents::fields.documents.original_date_sent'))
                ->disableInlineEdit()
                ->hidden(),

            DateTime::make('accepted_at', __('documents::fields.documents.accepted_at'))
                ->disableInlineEdit()
                ->hidden(),

            DateTime::make('owner_assigned_date', __('documents::fields.documents.owner_assigned_date'))
                ->disableInlineEdit()
                ->hidden(),

            DateTime::make('updated_at', __('core::app.updated_at'))
                ->disableInlineEdit()
                ->hidden(),

            DateTime::make('created_at', __('core::app.created_at'))
                ->disableInlineEdit()
                ->hidden(),
        ]);
    }

    /**
     * Get the resource available Filters
     */
    public function filters(ResourceRequest $request): array
    {
        return [
            TextFilter::make('title', __('documents::fields.documents.title'))->withoutNullOperators(),
            DocumentTypeFilter::make(),
            NumericFilter::make('amount', __('documents::fields.documents.amount')),
            DocumentBrandFilter::make(),
            DocumentStatusFilter::make(),
            DateTimeFilter::make('accepted_at', __('documents::fields.documents.accepted_at')),
            UserFilter::make(__('documents::fields.documents.user.name'))->withoutNullOperators(),
            ResourceUserTeamFilter::make(__('users::team.owner_team')),
            DateTimeFilter::make('owner_assigned_date', __('documents::fields.documents.owner_assigned_date')),
            BillableProductsFilter::make(),
            DateTimeFilter::make('last_date_sent', __('documents::fields.documents.last_date_sent')),
            DateTimeFilter::make('original_date_sent', __('documents::fields.documents.original_date_sent')),
            UserFilter::make(__('documents::document.sent_by'), 'sent_by')->canSeeWhen('view all documents'),
            UserFilter::make(__('core::app.created_by'), 'created_by')->withoutNullOperators()->canSeeWhen('view all documents'),
            DateTimeFilter::make('updated_at', __('core::app.updated_at')),
            DateTimeFilter::make('created_at', __('core::app.created_at')),
        ];
    }

    /**
     * Create the query when the resource is associated and the data is intended for the timeline.
     */
    public function timelineQuery(Model $subject, ResourceRequest $request): Builder
    {
        return parent::timelineQuery($subject, $request)->criteria($this->viewAuthorizedRecordsCriteria());
    }

    /**
     * Provides the resource available actions
     */
    public function actions(ResourceRequest $request): array
    {
        return [
            new \Modules\Users\Actions\AssignOwnerAction,

            (new DeleteAction)->authorizedToRunWhen(function (ActionRequest $request, Model $model, int $total) {
                return $request->user()->can($total > 1 ? 'bulkDelete' : 'delete', $model);
            }),
        ];
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('documents::document.documents');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('documents::document.document');
    }

    /**
     * Register permissions for the resource
     */
    public function registerPermissions(): void
    {
        $this->registerCommonPermissions();
    }

    /**
     * Register the settings menu items for the resource
     */
    public function settingsMenu(): array
    {
        return [
            SettingsMenuItem::make(__('documents::document.documents'), '/settings/documents', 'DocumentText')->order(23),
        ];
    }
}
