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

namespace Modules\MailClient\Resourceables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Core\Filters\DateTime as DateTimeFilter;
use Modules\Core\Filters\Radio as RadioFilter;
use Modules\Core\Filters\Tags as TagsFilter;
use Modules\Core\Filters\Text as TextFilter;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Http\Resources\TagResource;
use Modules\Core\Models\Model;
use Modules\Core\Table\Column;
use Modules\Core\Table\DateTimeColumn;
use Modules\Core\Table\HasOneColumn;
use Modules\Core\Table\Table;
use Modules\MailClient\Models\EmailAccountMessage;

class IncomingMessageTable extends Table
{
    /**
     * Additional attributes to be appended with the response.
     */
    protected array $appends = ['is_read'];

    /**
     * Additional relations to eager load for the query.
     * Eager load the folders as the folders are used to create the path.
     */
    protected array $with = ['tags', 'folders'];

    /**
     * Additional database columns to select for the table query.
     */
    protected array $select = [
        'is_read',
        'email_account_id', // uri key for json resource
    ];

    /**
     * Provide the table available default columns.
     */
    public function columns(): array
    {
        return [
            Column::make('subject', __('mailclient::inbox.subject'))->width('470px'),

            HasOneColumn::make('from', 'address', __('mailclient::inbox.from'))
                ->select('name'),

            DateTimeColumn::make('date', __('mailclient::inbox.date')),
        ];
    }

    /**
     * Get the resource available Filters
     */
    public function filters(ResourceRequest $request): array
    {
        return [
            TextFilter::make('subject', __('mailclient::inbox.subject')),

            TextFilter::make('to', __('mailclient::inbox.to'))->withoutNullOperators()
                ->query(function ($builder, $value, $condition, $sqlOperator) {
                    return $builder->whereHas(
                        'from',
                        fn (Builder $query) => $query->where(
                            'address',
                            $sqlOperator['operator'],
                            $value,
                            $condition
                        )->orWhere(
                            'name',
                            $sqlOperator['operator'],
                            $value,
                            $condition
                        )
                    );
                }),

            TextFilter::make('from', __('mailclient::inbox.from'))->withoutNullOperators()
                ->query(function ($builder, $value, $condition, $sqlOperator) {
                    return $builder->whereHas(
                        'to',
                        fn (Builder $query) => $query->where(
                            'address',
                            $sqlOperator['operator'],
                            $value,
                            $condition
                        )->orWhere(
                            'name',
                            $sqlOperator['operator'],
                            $value,
                            $condition
                        )
                    );
                }),

            DateTimeFilter::make('date', __('mailclient::inbox.date')),

            TagsFilter::make('tags', __('core::tags.tags'))->forType(EmailAccountMessage::TAGS_TYPE),

            RadioFilter::make('is_read', __('mailclient::inbox.filters.is_read'))->options([
                true => __('core::app.yes'),
                false => __('core::app.no'),
            ]),
        ];
    }

    /**
     * Create new row for the response.
     */
    protected function createRow(Model $model, Collection $columns): array
    {
        $row = parent::createRow($model, $columns);

        $row['tags'] = TagResource::collection($model->tags);

        return $row;
    }

    /**
     * Boot table
     */
    public function boot(): void
    {
        $this->orderBy('date', 'desc')->provideRowClassUsing(function (array $row) {
            return [
                'read' => $row['is_read'],
                'unread' => ! $row['is_read'],
            ];
        });
    }
}
