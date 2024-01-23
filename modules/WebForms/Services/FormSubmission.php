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

namespace Modules\WebForms\Services;

use Modules\Core\Facades\Format;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Models\Changelog;

class FormSubmission
{
    /**
     * Initialize new FormSubmission instance.
     */
    public function __construct(protected Changelog $changelog)
    {
    }

    /**
     * Get the web form submission data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function data()
    {
        return $this->changelog->properties;
    }

    /**
     * Parse the displayable value.
     *
     * @param  string|null  $value
     * @param  array  $property
     * @return string
     */
    protected function parseValue($value, $property)
    {
        if (! empty($value)) {
            if (isset($property['dateTime'])) {
                $value = Format::dateTime($value);
            } elseif (isset($property['date'])) {
                $value = Format::date($value);
            }
        }

        return $value !== null ? $value : '/';
    }

    /**
     * __toString
     */
    public function __toString(): string
    {
        $payload = '';

        foreach ($this->data() as $property) {
            $payload .= '<div>';
            $payload .= Innoclapps::resourceByName($property['resourceName'])->singularLabel();
            $payload .= '  '.'<span style="font-weight:bold;">'.$property['label'].'</span>';
            $payload .= '</div>';
            $payload .= '<p style="margin-top:5px;">';
            $payload .= $this->parseValue($property['value'], $property);
            $payload .= '</p>';
        }

        return $payload;
    }
}
