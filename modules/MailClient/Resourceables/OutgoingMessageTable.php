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

use Modules\Core\Table\Column;
use Modules\Core\Table\DateTimeColumn;
use Modules\Core\Table\HasManyColumn;

class OutgoingMessageTable extends IncomingMessageTable
{
    /**
     * Provides table available default columns
     */
    public function columns(): array
    {
        return [
            Column::make('subject', __('mailclient::inbox.subject'))->width('470px'),

            HasManyColumn::make('to', 'address', __('mailclient::inbox.to'))
                ->select('name'),

            DateTimeColumn::make('date', __('mailclient::inbox.date')),
        ];
    }
}
