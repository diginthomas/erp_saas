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

namespace Modules\Notes\Tests\Feature;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Contact;
use Modules\Deals\Models\Deal;
use Modules\Notes\Models\Note;
use Modules\Users\Models\User;
use Tests\TestCase;

class NoteModelTest extends TestCase
{
    public function test_when_note_user_id_not_provided_uses_current_user_id()
    {
        $user = $this->signIn();

        $note = Note::factory(['user_id' => null])->create();

        $this->assertEquals($note->user_id, $user->id);
    }

    public function test_note_user_id_can_be_provided()
    {
        $user = $this->createUser();

        $note = Note::factory()->for($user)->create();

        $this->assertEquals($note->user_id, $user->id);
    }

    public function test_note_has_companies()
    {
        $note = Note::factory()->has(Company::factory()->count(2))->create();

        $this->assertCount(2, $note->companies);
    }

    public function test_note_has_contacts()
    {
        $note = Note::factory()->has(Contact::factory()->count(2))->create();

        $this->assertCount(2, $note->contacts);
    }

    public function test_note_has_deals()
    {
        $note = Note::factory()->has(Deal::factory()->count(2))->create();

        $this->assertCount(2, $note->deals);
    }

    public function test_note_has_user()
    {
        $note = Note::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $note->user);
    }

    public function test_note_has_comments()
    {
        $note = new Note;

        $this->assertInstanceof(MorphMany::class, $note->comments());
    }
}
