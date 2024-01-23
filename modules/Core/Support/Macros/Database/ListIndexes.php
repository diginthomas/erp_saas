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

namespace Modules\Core\Support\Macros\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ListIndexes
{
    /**
     * List indexes from the given table or model, optionally provide a column.
     */
    public function __invoke(string|Model $table, ?string $column = null): array
    {
        $tableName = $table instanceof Model ? $table->getTable() : $table;

        $indexes = Schema::getIndexes($tableName);

        if ($column) {
            return collect($indexes)->filter(fn (array $index) => in_array($column, $index['columns']))->values()->all();
        }

        return $indexes;
    }
}
