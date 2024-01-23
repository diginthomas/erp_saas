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

namespace Modules\MailClient\Concerns;

use Exception;
use Modules\MailClient\Client\Compose\Message;
use Modules\MailClient\Client\Compose\MessageForward;
use Modules\MailClient\Client\Compose\MessageReply;
use Modules\MailClient\Client\Contracts\MessageInterface;
use Modules\MailClient\Models\EmailAccount;
use Modules\MailClient\Models\EmailAccountMessage;
use Modules\MailClient\Models\ScheduledEmail;
use Modules\MailClient\Services\EmailAccountMessageSyncService;
use Throwable;

trait SendsScheduledEmail
{
    use InteractsWithEmailMessageAssociations;

    /**
     * The number of minutes to retry failed messages.
     */
    protected int $retryAfter = 60;

    /**
     * Send the message.
     */
    public function send()
    {
        try {
            if (! $this->isSending()) {
                $this->markAsSending();
            }

            if (! $this->account->canSendEmail()) {
                throw new Exception(
                    'Account is unable to send emails: '.$this->account->sync_state_comment
                );
            }

            $remoteMessage = $this->performSend();

            $this->markAsSent();

            if ($remoteMessage) {
                (new EmailAccountMessageSyncService)->create(
                    $this->account->id,
                    $remoteMessage,
                    $this->associations ?: []
                );
            }
        } catch (Throwable $e) {
            $retries = $this->retries + 1;
            $isFinalRetry = $retries >= ScheduledEmail::$maxRetries;

            $this->markAsFailed($e->getMessage(), [
                'retry_after' => ! $isFinalRetry ? now()->addMinutes($this->retryAfter) : null,
                'retries' => $retries,
                'failed_at' => $isFinalRetry ? now() : null,
            ]);
        }
    }

    /**
     * Send the scheduled message via the mail client.
     */
    protected function performSend(): ?MessageInterface
    {
        $composer = $this->createComposer(
            $this->type,
            $this->account,
            $this->related_message_id
        );

        $this->addComposerAssociationsHeaders($composer, $this->associations ?: []);

        foreach ($this->media as $attachment) {
            $composer->attachFromStorageDisk(
                $attachment->disk,
                $attachment->getDiskPath(),
                $attachment->basename
            );
        }

        return $composer->subject($this->subject)
            ->to($this->to)
            ->bcc($this->bcc)
            ->cc($this->cc)
            ->htmlBody($this->html_body)
            ->withTrackers()
            ->send();

    }

    /**
     * Create the message composer.
     */
    protected function createComposer(string $type, EmailAccount $account, ?int $relatedMessageId): Message|MessageReply|MessageForward
    {
        $relatedMessage = $relatedMessageId ? EmailAccountMessage::find($relatedMessageId) : null;

        return match ($type) {
            'send' => new Message(
                $account->createClient(),
                $account->sentFolder->identifier()
            ),
            'reply' => new MessageReply(
                $account->createClient(),
                $relatedMessage->remote_id,
                $relatedMessage->folders->first()->identifier(),
                $account->sentFolder->identifier()
            ),
            'forward' => new MessageForward(
                $account->createClient(),
                $relatedMessage->remote_id,
                $relatedMessage->folders->first()->identifier(),
                $account->sentFolder->identifier()
            )
        };
    }
}
