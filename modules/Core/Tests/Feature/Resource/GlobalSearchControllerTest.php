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

namespace Modules\Core\Tests\Feature\Resource;

use Modules\Contacts\Models\Contact;
use Modules\Contacts\Resourceables\Contact\Contact as ContactResource;
use Tests\TestCase;

class GlobalSearchControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_the_search_endpoints()
    {
        $this->getJson('/api/search')->assertUnauthorized();
    }

    public function test_own_criteria_is_applied_on_global_searching()
    {
        $user = $this->asRegularUser()->withPermissionsTo('view own contacts')->signIn();
        $user1 = $this->createUser();

        Contact::factory()->for($user1)->create(['first_name' => 'John Doe']);
        $record = Contact::factory()->for($user)->create(['first_name' => 'John Concord']);

        $this->getJson('/api/search?q=John')
            ->assertJsonCount(1, '0.data')
            ->assertJsonPath('0.data.0.id', $record->id)
            ->assertJsonPath('0.data.0.path', $record->path)
            ->assertJsonPath('0.data.0.display_name', $record->display_name);
    }

    public function test_it_returns_all_attributes_when_global_searching()
    {
        $this->signIn();
        Contact::factory()->create(['first_name' => 'John']);

        $this->getJson('/api/search?q=john')
            ->assertOk()
            ->assertJsonStructure([
                '0' => [
                    'data' => [
                        '0' => [
                            'path',
                            'display_name',
                            'created_at',
                            'id',
                        ],
                    ],
                ],
            ]);
    }

    public function test_non_searchable_resource_cannot_be_searched()
    {
        $this->signIn();

        ContactResource::$globallySearchable = false;

        $contact = Contact::factory()->create(['first_name' => 'Non Searchable']);

        $this->getJson('/api/search?q='.$contact->title)
            ->assertJsonCount(0);

        ContactResource::$globallySearchable = true;
    }

    public function test_it_does_not_return_any_results_if_search_query_is_empty()
    {
        $this->signIn();

        Contact::factory()->create();

        $this->getJson('/api/search?q=')
            ->assertJsonCount(0);
    }
}
