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

namespace App\Http\View\FrontendComposers;

use JsonSerializable;

class Template implements JsonSerializable
{
    public ?Component $detailComponent = null;

    /**
     * Set the view component instance.
     */
    public function detailComponent(Component $component): static
    {
        $this->detailComponent = $component;

        return $this;
    }

    /**
     * Prepare the template for front-end
     */
    public function jsonSerialize(): array
    {
        return [
            'detail' => $this->detailComponent,
        ];
    }
}
