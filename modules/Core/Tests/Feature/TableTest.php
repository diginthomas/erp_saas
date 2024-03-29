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

namespace Modules\Core\Tests\Feature;

use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Table\Exceptions\OrderByNonExistingColumnException;
use Tests\Fixtures\EventTable;
use Tests\TestCase;

class TableTest extends TestCase
{
    public function test_user_cannot_sort_table_field_that_is_not_added_in_table_columns()
    {
        $user = $this->signIn();

        $request = app(ResourceRequest::class)->setUserResolver(function () use ($user) {
            return $user;
        });

        $table = (new EventTable(null, $request))->orderBy('non-existent-field', 'desc');

        $this->expectException(OrderByNonExistingColumnException::class);

        $table->settings()->toArray();
    }
}
