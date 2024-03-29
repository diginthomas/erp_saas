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

namespace Modules\Users\Tests\Feature;

use Illuminate\Support\Facades\Notification;
use Modules\Users\Mention\PendingMention;
use Modules\Users\Notifications\UserMentioned;
use Modules\Users\Tests\Concerns\TestsMentions;
use Tests\TestCase;

class PendingMentionTest extends TestCase
{
    use TestsMentions;

    public function test_mention_attributes_are_updated_properly()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $mention = new PendingMention($this->mentionText($user1->id, 'User 1').$this->mentionText($user2->id, 'User 2'));

        $expected = $this->mentionText($user1->id, 'User 1', 'true').$this->mentionText($user2->id, 'User 2', 'true');
        $this->assertEquals($expected, $mention->getUpdatedText());

        $mention = new PendingMention($this->mentionText($user1->id, 'User 1').$this->mentionText($user2->id, 'User 2', 'true'));

        $expected = $this->mentionText($user1->id, 'User 1', 'true').$this->mentionText($user2->id, 'User 2', 'true');
        $this->assertEquals($expected, $mention->getUpdatedText());
    }

    public function test_mention_has_url()
    {
        $mention = new PendingMention($this->mentionText(1, 'User 1'));
        $mention->setUrl('/dashboard');

        $this->assertEquals('/dashboard', $mention->getMentionUrl());
    }

    public function test_mention_url_has_query_parameters()
    {
        $mention = new PendingMention($this->mentionText(1, 'User 1'));

        $mention->setUrl('/dashboard')
            ->withUrlQueryParameter(['resource_id' => 1, 'section' => 'timeline'])
            ->withUrlQueryParameter('key', 'value');

        $this->assertEquals('/dashboard?resource_id=1&section=timeline&key=value', $mention->getMentionUrl());
    }

    public function test_user_can_be_mentioned()
    {
        $user = $this->createUser();
        $mentioner = $this->createUser();

        $mention = new PendingMention(
            $this->mentionText($user->id, $user->name)
        );

        Notification::fake();

        $mention->setUrl('/dashboard')->notify($mentioner);

        Notification::assertSentTo([$user], UserMentioned::class);
    }

    public function test_multiple_users_can_be_mentioned()
    {
        $user = $this->createUser();
        $user1 = $this->createUser();
        $mentioner = $this->createUser();

        $mention = new PendingMention(
            $this->mentionText($user->id, $user->name).$this->mentionText($user1->id, $user1->name)
        );

        Notification::fake();

        $mention->setUrl('/dashboard')->notify($mentioner);

        Notification::assertSentTo([$user, $user1], UserMentioned::class);
    }

    public function test_already_mention_users_are_not_notified()
    {
        $user = $this->createUser();
        $user1 = $this->createUser();
        $mentioner = $this->createUser();

        $mention = new PendingMention(
            $this->mentionText($user->id, $user->name).$this->mentionText($user1->id, $user1->name, 'true')
        );

        Notification::fake();

        $mention->setUrl('/dashboard')->notify($mentioner);

        Notification::assertSentTimes(UserMentioned::class, 1);
        Notification::assertNotSentTo([$user1], UserMentioned::class);
    }
}
