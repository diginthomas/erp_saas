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

trait Selectable
{
    /**
     * Indicates that the "X" select field is hidden.
     */
    public function withoutClearAction(): static
    {
        $this->withMeta(['attributes' => ['clearable' => false]]);

        return $this;
    }

    /**
     * Check whether the field is async.
     */
    public function isAsync(): bool
    {
        return isset($this->meta['asyncUrl']);
    }

    /**
     * Set async URL for searching
     */
    public function async(string $asyncUrl): static
    {
        $this->withMeta([
            'asyncUrl' => $asyncUrl,
            // Automatically add placeholder "Type to search..." on async fields
            'attributes' => ['placeholder' => __('core::app.type_to_search')],
        ]);

        return $this;
    }

    /**
     * Set the URL to lazy load options when the field is first opened
     */
    public function lazyLoad(string $url, array $params = []): static
    {
        $this->withMeta(['lazyLoad' => [
            'url' => $url,
            'params' => $params,
        ]]);

        return $this;
    }
}
