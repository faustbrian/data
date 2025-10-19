<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Attributes\Validation;

use Attribute;
use Override;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

use function array_filter;

/**
 * Validates that a field is a decimal number with a specified number of decimal places.
 *
 * This attribute ensures the field value is a decimal number with the
 * minimum and optionally maximum number of decimal places. Useful for
 * validating prices, percentages, or other numeric values that require
 * specific decimal precision.
 *
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Decimal extends StringValidationAttribute
{
    /**
     * Create a new decimal validation attribute instance.
     *
     * @param float|int|RouteParameterReference      $min Minimum number of decimal places required.
     *                                                    Can be a literal value or a route parameter
     *                                                    reference for dynamic validation rules based
     *                                                    on URL parameters.
     * @param null|float|int|RouteParameterReference $max Maximum number of decimal places allowed.
     *                                                    Optional upper bound for decimal precision.
     *                                                    When null, only minimum places are enforced.
     *                                                    Can reference route parameters for dynamic
     *                                                    validation constraints.
     */
    public function __construct(
        private readonly int|float|RouteParameterReference $min,
        private readonly null|int|float|RouteParameterReference $max = null,
    ) {}

    /**
     * Returns the Laravel validation rule keyword.
     *
     * @return string The validation rule keyword 'decimal'
     */
    #[Override()]
    public static function keyword(): string
    {
        return 'decimal';
    }

    /**
     * Returns the parameters for the validation rule.
     *
     * Filters out null values to only include the minimum and optionally
     * the maximum decimal places in the validation rule parameters.
     *
     * @return array<int, mixed> Array containing min and optionally max decimal places
     */
    #[Override()]
    public function parameters(): array
    {
        return array_filter([$this->min, $this->max], fn (RouteParameterReference|int|float|null $value): bool => $value !== null);
    }
}
