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

use Modules\Core\Facades\Fields;
use Modules\Core\Fields\Email;
use Modules\Core\Fields\Text;
use Tests\Fixtures\Event;
use Tests\TestCase;

class FieldControllerTest extends TestCase
{
    public function test_resource_create_fields_can_be_retrieved()
    {
        $this->signIn();

        Fields::replace('events', [
            Text::make('title'),
            Text::make('description'),
            Email::make('email')->hideWhenCreating(),
        ]);

        $this->getJson('/api/events/create-fields')->assertJsonCount(2);
    }

    public function test_resource_update_fields_can_be_retrieved()
    {
        $this->signIn();

        $event = Event::factory()->create();

        Fields::replace('events', [
            Text::make('title'),
            Text::make('description')->hideWhenUpdating(),
        ]);

        $this->getJson('/api/events/'.$event->id.'/update-fields?intent=update')->assertJsonCount(1);
    }

    public function test_resource_detail_fields_can_be_retrieved()
    {
        $this->signIn();

        $event = Event::factory()->create();

        Fields::replace('events', [
            Text::make('title'),
            Text::make('description')->hideFromDetail(),
        ]);

        $this->getJson('/api/events/'.$event->id.'/detail-fields?intent=detail')->assertJsonCount(1);
    }

    public function test_unauthorized_user_cannot_see_fields_that_is_not_allowed_to_see()
    {
        $this->asRegularUser()->signIn();

        $event = Event::factory()->create();

        Fields::replace('events', function () {
            return [
                Text::make('test', 'test'),
                Text::make('test', 'test')->canSeeWhen('DUMMY_ABILITY', 'DUMMY_MODEL'),
                Text::make('test', 'test')->canSee(function () {
                    return false;
                }),
            ];
        });

        $this->getJson('/api/events/'.$event->id.'/update-fields')->assertJsonCount(1);
    }

    public function test_super_admin_can_see_all_fields_that_are_authorized_via_gate()
    {
        $this->signIn();

        Fields::replace('events', function () {
            return [
                Text::make('test', 'test')->canSeeWhen('DUMMY_ABILITY', 'DUMMY_MODEL'),
                Text::make('test', 'test')->canSeeWhen('DUMMY_ABILITY', 'DUMMY_MODEL'),
            ];
        });

        $this->getJson('/api/events/create-fields')->assertJsonCount(2);
    }

    public function test_super_admin_cannot_see_fields_that_are_authorized_via_closure_by_returning_false()
    {
        $this->signIn();

        Fields::replace('events', function () {
            return [
                Text::make('test', 'test'),
                Text::make('test', 'test')->canSee(function () {
                    // If returned false directly and the check is not
                    // performed via gate, it should not be visible to super
                    // admin either
                    return false;
                }),
            ];
        });

        $this->getJson('/api/events/create-fields')->assertJsonCount(1);
    }
}
