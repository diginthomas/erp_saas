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

/**
 *   USAGE:
 *   OperandFilter::make('revenue', 'Revenue')->setOperands([
 *       (new Operand('total_revenue', 'Total Revenue'))->filter(NumericFilter::class),
 *       (new Operand('annual_revenue', 'Annual Revenue'))->filter(NumericFilter::class),
 *   [),
 */
class OperandFilter extends Filter
{
    /**
     * Filter current operand.
     */
    protected ?string $operand = null;

    /**
     * Filter current operands.
     */
    protected ?array $operands = null;

    /**
     * Set the filter operand.
     */
    public function setOperand(string $operand): static
    {
        $this->operand = $operand;

        return $this;
    }

    /**
     * Get the filter selected operand.
     */
    public function getOperand(): ?string
    {
        return $this->operand;
    }

    /**
     * Set the filter operands.
     */
    public function setOperands(array $operands): static
    {
        $this->operands = $operands;

        return $this;
    }

    /**
     * Get the filter operands.
     */
    public function getOperands(): ?array
    {
        return $this->operands;
    }

    /**
     * Check whether the filter has operands.
     */
    public function hasOperands(): bool
    {
        return is_array($this->operands) && count($this->operands) > 0;
    }

    /**
     * Find operand filter by given value.
     */
    public function findOperand($value): ?Operand
    {
        return collect($this->getOperands())->first(fn ($operand) => $operand->value == $value);
    }

    /**
     * Hide the filter operands.
     *
     * Useful when only 1 operand is used, which is by default pre-selected.
     */
    public function hideOperands(bool $bool = true): static
    {
        $this->withMeta([__FUNCTION__ => $bool]);

        return $this;
    }

    /**
     * Defines a filter type.
     */
    public function type(): string
    {
        return 'nullable';
    }
}
