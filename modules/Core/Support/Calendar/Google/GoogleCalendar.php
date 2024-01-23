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

namespace Modules\Core\Support\Calendar\Google;

use Google\Service\Exception as GoogleServiceException;
use Modules\Core\Contracts\OAuth\Calendarable;
use Modules\Core\Facades\Google as Client;
use Modules\Core\Support\Calendar\Exceptions\UnauthorizedException;
use Modules\Core\Support\OAuth\AccessTokenProvider;

class GoogleCalendar implements Calendarable
{
    /**
     * Initialize new GoogleCalendar instance.
     */
    public function __construct(protected AccessTokenProvider $token)
    {
        Client::connectUsing($token->getEmail());
    }

    /**
     * Get the available calendars.
     *
     * @return \Modules\Core\Contracts\Calendar\Calendar[]
     */
    public function getCalendars()
    {
        try {
            return collect(Client::calendar()->list())
                ->mapInto(Calendar::class)
                ->all();
        } catch (GoogleServiceException $e) {
            $message = $e->getErrors()[0]['message'] ?? $e->getMessage();

            if ($e->getCode() == 403) {
                throw new UnauthorizedException($message, $e->getCode(), $e);
            }

            throw $e;
        }
    }
}
