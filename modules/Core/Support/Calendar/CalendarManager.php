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

namespace Modules\Core\Support\Calendar;

use InvalidArgumentException;
use Modules\Core\Contracts\OAuth\Calendarable;
use Modules\Core\Support\Calendar\Google\GoogleCalendar;
use Modules\Core\Support\Calendar\Outlook\OutlookCalendar;
use Modules\Core\Support\OAuth\AccessTokenProvider;

class CalendarManager
{
    /**
     * Create calendar client.
     */
    public static function createClient(string $connectionType, AccessTokenProvider $token): Calendarable
    {
        $method = 'create'.ucfirst($connectionType).'Driver';

        if (! method_exists(new static, $method)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve [%s] driver for [%s].',
                $method,
                static::class
            ));
        }

        return self::$method($token);
    }

    /**
     * Create the Google calendar driver.
     */
    public static function createGoogleDriver(AccessTokenProvider $token): GoogleCalendar&Calendarable
    {
        return new GoogleCalendar($token);
    }

    /**
     * Create the Outlook calendar driver.
     */
    public static function createOutlookDriver(AccessTokenProvider $token): OutlookCalendar&Calendarable
    {
        return new OutlookCalendar($token);
    }
}
