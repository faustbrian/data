<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Casts;

use Attribute;
use Override;
use Spatie\LaravelData\Attributes\GetsCast;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

use const PHP_ROUND_HALF_UP;

use function is_float;
use function is_int;
use function is_numeric;
use function round;

/**
 * Rounds numeric values to a specified precision using configurable rounding modes.
 *
 * This cast applies PHP's round() function to numeric properties, supporting decimal
 * precision control and various rounding modes (half up, half down, half even, etc.).
 * Non-numeric values pass through unchanged. Particularly useful for financial
 * calculations, display formatting, and ensuring consistent decimal precision.
 *
 * ```php
 * use Cline\Data\Casts\RoundCast;
 *
 * final class OrderData extends Data
 * {
 *     public function __construct(
 *         #[RoundCast(precision: 2)]
 *         public float $total, // 10.999 becomes 11.00
 *         #[RoundCast(precision: 2, mode: PHP_ROUND_HALF_DOWN)]
 *         public float $discount, // 5.555 becomes 5.55
 *         #[RoundCast(precision: 0)]
 *         public float $quantity, // 42.7 becomes 43.0
 *     ) {}
 * }
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class RoundCast implements Cast, GetsCast
{
    /**
     * Create a new RoundCast instance with precision and rounding mode.
     *
     * @param int $precision Number of decimal digits to round to. Positive values specify
     *                       decimal places after the decimal point (e.g., 2 for currency).
     *                       Zero rounds to the nearest integer. Negative values round to
     *                       the left of the decimal point (e.g., -1 rounds to nearest 10).
     * @param int $mode      PHP rounding mode constant that determines tie-breaking behavior.
     *                       Common values: PHP_ROUND_HALF_UP (default, rounds .5 up),
     *                       PHP_ROUND_HALF_DOWN (rounds .5 down), PHP_ROUND_HALF_EVEN
     *                       (banker's rounding), PHP_ROUND_HALF_ODD. Mode affects how values
     *                       exactly halfway between two numbers are rounded.
     */
    public function __construct(
        private int $precision = 0,
        private int $mode = PHP_ROUND_HALF_UP,
    ) {}

    /**
     * Returns the cast instance for Laravel Data's cast resolution.
     *
     * @return Cast The current cast instance
     */
    public function get(): Cast
    {
        return $this;
    }

    /**
     * Rounds numeric values to the configured precision using the specified mode.
     *
     * Converts the value to float before rounding to ensure consistent behavior
     * with numeric strings. Non-numeric values are returned unchanged to preserve
     * their original type and prevent casting errors.
     *
     * @param  DataProperty         $property   The property being cast (unused but required by interface)
     * @param  mixed                $value      The raw value to cast, expected to be numeric but may be any type
     * @param  array<string, mixed> $properties All properties being cast in the current context
     * @param  CreationContext      $context    Metadata about the data object creation process
     * @return mixed                The rounded float value if input is numeric, otherwise the original value
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_int($value) && !is_float($value) && !is_numeric($value)) {
            return $value;
        }

        return round((float) $value, $this->precision, $this->mode);
    }
}
