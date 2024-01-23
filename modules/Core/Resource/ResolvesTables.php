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

namespace Modules\Core\Resource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\Field;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Table\Column;
use Modules\Core\Table\Table;

/**
 * @mixin \Modules\Core\Resource\Resource
 */
trait ResolvesTables
{
    /**
     * Create new table instance.
     */
    protected function newTable(Builder $query, ResourceRequest $request, ?string $identifier = null): Table
    {
        // Perform count before any where (except authorizations related) queries are performed.
        $preTotal = with(clone $query)->count();

        return $this->table($query, $request)
            ->setIdentifier($identifier ?: $this->name())
            ->setPreTotal($preTotal);
    }

    /**
     * Resolve the resource table class
     */
    public function resolveTable(ResourceRequest $request): Table
    {
        $query = $this->tableQuery($request);

        $table = tap($this->newTable($query, $request), function ($table) {
            $table->setColumns(
                $table->getColumns()->push(
                    ...$this->columnsFromFields($this->fieldsForIndex())
                )
            );
        });

        // We will check if the tables has table wide actions and filters defined
        // If there are no table wide actions and filters, in this case, we will
        // set the table actions and filters directly from the resource defined.
        if ($table->resolveFilters($request)->isEmpty()) {
            $table->setFilters($this->filtersForResource($request));
        }

        if ($table->resolveActions($request)->isEmpty()) {
            $table->setActions($this->actionsForResource($request));
        }

        return $table;
    }

    /**
     * Resolve the resource trashed table class
     */
    public function resolveTrashedTable(ResourceRequest $request): Table
    {
        $query = $this->tableQuery($request)->onlyTrashed();
        $table = $this->newTable($query, $request, $this->name().'-trashed');

        return $table->clearOrderBy()
            ->setColumns($this->columnsForTrashedTable($query))
            ->setActions($table->trashedViewActions())
            ->orderBy($query->getModel()->getDeletedAtColumn())
            ->withoutActionsColumn()
            ->customizeable(false);
    }

    /**
     * Get the columns for trashed table.
     */
    public function columnsForTrashedTable(Builder $query): Collection
    {
        Column::$trashed = true;

        $columns = $this->columnsFromFields(
            $this->fieldsForIndex()->prepend(
                DateTime::make($query->getModel()->getDeletedAtColumn(), __('core::app.deleted_at'))
                    ->tapIndexColumn(
                        fn (Column $column) => $column->width('250px')->minWidth('250px')
                    )
            )->each->disableInlineEdit()
        )->each(function (Column $column) {
            $column->primary(false);
        });

        Column::$trashed = false;

        return $columns;
    }

    /**
     * Get the table columns from fields
     */
    public function columnsFromFields(Collection $fields): Collection
    {
        return $fields->map(function (Field $field) {
            return $field->resolveIndexColumn();
        })->filter();
    }
}
