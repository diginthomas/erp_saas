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

namespace Modules\Contacts\Resourceables\Contact;

use Modules\Contacts\Models\Contact;
use Modules\Core\Table\Table;

class ContactTable extends Table
{
    /**
     * Whether the table has actions column.
     */
    public bool $withActionsColumn = true;

    /**
     * Indicates whether the user can customize columns orders and visibility
     */
    public bool $customizeable = true;

    /**
     * Prepare the searchable columns for the model from the table defined columns.
     */
    public function prepareSearchableColumns(): array
    {
        return array_merge(
            parent::prepareSearchableColumns(),
            ['full_name' => [
                'column' => Contact::nameQueryExpression(),
                'condition' => 'like',
            ]],
        );
    }

    /**
     * Boot table
     */
    public function boot(): void
    {
        $this->orderBy('created_at', 'desc');
    }
}
