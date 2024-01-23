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

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Table\Table;
use Modules\Deals\Criteria\ViewAuthorizedDealsCriteria;
use Modules\Deals\Models\Deal;
use Modules\Deals\Models\Stage;

class DealTable extends Table
{
    /**
     * Additional database columns to select for the table query.
     */
    protected array $select = [
        'user_id', // user_id is for the policy checks
        'expected_close_date', // falls_behind_expected_close_date check
        'status', // falls_behind_expected_close_date check
    ];

    /**
     * Attributes to be appended with the response.
     */
    protected array $appends = [
        'falls_behind_expected_close_date', // row class
    ];

    /**
     * Whether the table columns can be customized.
     */
    public bool $customizeable = true;

    /**
     * Whether the table has actions column.
     */
    public bool $withActionsColumn = true;

    /**
     * Tap the response
     */
    protected function tapResponse(LengthAwarePaginator $response): void
    {
        $query = Deal::criteria([
            $this->newRequestCriteria(),
            $this->newFilterRulesCriteria(),
            ViewAuthorizedDealsCriteria::class,
        ]);

        $summary = Stage::summary($query);

        $this->meta = ['summary' => [
            'count' => $summary->sum('count'),
            'value' => $summary->sum('value'),
            'weighted_value' => $summary->sum('weighted_value'),
        ]];
    }

    /**
     * Boot table
     */
    public function boot(): void
    {
        $this->orderBy('created_at', 'desc')->provideRowClassUsing(function (array $row) {
            return [
                'row-border-left' => true,
                'row-border-warning' => $row['falls_behind_expected_close_date'],
            ];
        });
    }
}
