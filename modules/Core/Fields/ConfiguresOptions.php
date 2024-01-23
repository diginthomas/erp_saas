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

namespace Modules\Core\Fields;

trait ConfiguresOptions
{
    /**
     * Add path to redirect on option click for the detail and index view.
     */
    public function onOptionClickRedirectTo(string $path): static
    {
        $this->withMeta(['onOptionClickRedirectTo' => $path]);

        return $this;
    }

    /**
     * Set that the options should be displayed as pills on detail and index view.
     */
    public function displayAsPills(): static
    {
        $this->withMeta(['displayAsPills' => true]);

        return $this;
    }

    /**
     * Set that the options should be displayed on new line on detail and index view.
     */
    public function eachOnNewLine(): static
    {
        $this->withMeta(['eachOnNewLine' => true]);

        return $this;
    }
}
