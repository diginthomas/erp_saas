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

namespace Modules\MailClient\Client\Compose;

class MessageForward extends MessageReply
{
    /**
     * Forward the message.
     *
     * @return \Modules\MailClient\Client\Contracts\MessageInterface
     */
    public function send()
    {
        return $this->client->forward($this->remoteId, $this->folder);
    }
}
