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

namespace Modules\Core\Support\Calendar\Outlook;

use Modules\Core\Support\Calendar\AbstractCalendar;

class Calendar extends AbstractCalendar
{
    /**
     * Get the calendar ID.
     */
    public function getId(): string
    {
        return $this->getEntity()->getId();
    }

    /**
     * Get the calendar title.
     */
    public function getTitle(): string
    {
        return $this->getEntity()->getName();
    }

    /**
     * Check whether the calendar is default.
     */
    public function isDefault(): bool
    {
        return $this->getEntity()->getIsDefaultCalendar();
    }
}
