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

namespace Modules\Core\Filters;

use Exception;
use JsonSerializable;
use Modules\Core\Support\Makeable;

class Operand implements JsonSerializable
{
    use Makeable;

    /**
     * The rule the oprand is related to.
     */
    public ?Filter $rule = null;

    /**
     * From where the value key should be taken
     */
    public string $valueKey = 'value';

    /**
     * From where the label key should be taken
     */
    public string $labelKey = 'label';

    /**
     * Initialize Operand class
     *
     * @param  mixed  $value
     * @param  string  $label
     */
    public function __construct(public $value, public $label)
    {
    }

    /**
     * Set the operand filter
     *
     * @param  \Modules\Core\Filters\Fitler|string  $rule
     * @return \Modules\Core\Filters\Operand
     */
    public function filter($rule)
    {
        if (is_string($rule)) {
            $rule = $rule::make($this->value);
        }

        if ($rule instanceof HasMany) {
            throw new Exception('Cannot use HasMany filter in operands');
        }

        $this->rule = $rule;

        return $this;
    }

    /**
     * Set custom key for value.
     */
    public function valueKey(string $key): static
    {
        $this->valueKey = $key;

        return $this;
    }

    /**
     * Set custom label key.
     */
    public function labelKey(string $key): static
    {
        $this->labelKey = $key;

        return $this;
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'valueKey' => $this->valueKey,
            'labelKey' => $this->labelKey,
            'rule' => $this->rule,
        ];
    }
}
