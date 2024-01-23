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

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Str;
use Modules\Core\Contracts\HasNotificationsSettings;

class Notification extends BaseNotification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     */
    public function via(HasNotificationsSettings $notifiable): array
    {
        $settings = $notifiable->getNotificationPreference(static::key());

        if (count($settings) === 0) {
            return static::availableChannels();
        }

        // Filter the channels the user specifically turned off notifications
        $except = array_keys(array_filter($settings, fn ($notify) => $notify === false));

        return array_values(array_diff(static::availableChannels(), $except));
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        if (Notifications::disabled()) {
            return false;
        }

        if (! $notifiable instanceof HasNotificationsSettings) {
            return true;
        }

        // When the user turned off all notifications, only the broadcast will be available,
        // in this case, we don't need to send any notification as the broadcast will broadcast invalid notification.
        return ! ($channel === 'broadcast' && count($this->via($notifiable)) === 1);
    }

    /**
     * Provide the notification available delivery channels
     */
    public static function availableChannels(): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Get the notification unique key identifier
     */
    public static function key(): string
    {
        return Str::snake(class_basename(get_called_class()), '-');
    }

    /**
     * Get the displayable name of the notification
     */
    public static function name(): string
    {
        return Str::title(Str::snake(class_basename(get_called_class()), ' '));
    }

    /**
     * Get the notification description
     */
    public static function description(): ?string
    {
        return null;
    }

    /**
     * Define whether the notification is user-configurable
     */
    public static function configurable(): bool
    {
        return true;
    }
}
