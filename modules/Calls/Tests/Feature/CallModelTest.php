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

namespace Modules\Calls\Tests\Feature;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Calls\Models\Call;
use Modules\Calls\Models\CallOutcome;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Contact;
use Modules\Deals\Models\Deal;
use Modules\Users\Models\User;
use Tests\TestCase;

class CallModelTest extends TestCase
{
    public function test_when_call_user_id_not_provided_uses_current_user_id()
    {
        $user = $this->signIn();

        $call = Call::factory(['user_id' => null])->create();

        $this->assertEquals($call->user_id, $user->id);
    }

    public function test_call_user_id_can_be_provided()
    {
        $user = $this->createUser();

        $call = Call::factory()->for($user)->create();

        $this->assertEquals($call->user_id, $user->id);
    }

    public function test_call_has_outcome()
    {
        $call = Call::factory()->for(CallOutcome::factory(), 'outcome')->create();

        $this->assertInstanceOf(CallOutcome::class, $call->outcome);
    }

    public function test_call_has_companies()
    {
        $call = Call::factory()->has(Company::factory()->count(2))->create();

        $this->assertCount(2, $call->companies);
    }

    public function test_call_has_contacts()
    {
        $call = Call::factory()->has(Contact::factory()->count(2))->create();

        $this->assertCount(2, $call->contacts);
    }

    public function test_call_has_deals()
    {
        $call = Call::factory()->has(Deal::factory()->count(2))->create();

        $this->assertCount(2, $call->deals);
    }

    public function test_call_has_user()
    {
        $call = Call::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $call->user);
    }

    public function test_call_has_comments()
    {
        $call = new Call;

        $this->assertInstanceof(MorphMany::class, $call->comments());
    }

    public function test_outcome_with_calls_cannot_be_deleted()
    {
        $outcome = CallOutcome::factory()->has(Call::factory())->create();

        $this->expectExceptionMessage(__('calls::call.outcome.delete_warning'));

        $outcome->delete();
    }
}
