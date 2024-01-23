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

namespace Modules\Core\Notifications;

use Modules\Core\Contracts\HasNotificationsSettings;

class Notifications
{
    /**
     * All of the registered notifications.
     */
    protected static array $registered = [];

    /**
     * Indicates if all the notifications are disabled.
     */
    protected static bool $disabled = false;

    /**
     * Disable all of the notifications.
     */
    public static function disable(): void
    {
        static::$disabled = true;
    }

    /**
     * After disabling, enable all of the notifications again.
     */
    public static function enable(): void
    {
        static::$disabled = false;
    }

    /**
     * Check if all of the notifications are disabled.
     */
    public static function disabled(): bool
    {
        return static::$disabled;
    }

    /**
     * Register the given notifications.
     */
    public static function register(array $notifications): void
    {
        static::$registered = array_unique(
            array_merge(static::$registered, $notifications)
        );
    }

    /**
     * Get all the notifications information for front-end.
     */
    public static function preferences(?HasNotificationsSettings $notifiable = null): array
    {
        return collect(static::$registered)->filter(function ($notification) {
            return $notification::configurable();
        })->map(function ($notification) use ($notifiable) {
            return array_merge([
                'key' => $notification::key(),
                'name' => $notification::name(),
                'description' => $notification::description(),

                'channels' => $channels = collect($notification::availableChannels())
                    ->reject(fn ($channel) => $channel === 'broadcast')->values(),

            ], is_null($notifiable) ? [] : ['availability' => array_merge(
                $channels->mapWithKeys(fn ($channel) => [$channel => true])->all(),
                $notifiable->getNotificationPreference($notification::key())
            )]);
        })->values()->all();
    }
}
