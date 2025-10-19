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

use function floor;
use function is_float;
use function is_int;
use function is_numeric;

/**
 * Rounds numeric values down to the nearest integer using PHP's floor function.
 *
 * Applies mathematical floor operation to convert floats and numeric strings
 * to their next lowest integer value. Useful for quantity calculations, truncating
 * decimal precision, and scenarios requiring conservative downward rounding.
 * Non-numeric values are passed through unchanged.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute()]
final class FloorCast implements Cast, GetsCast
{
    /**
     * Returns the cast instance for the Spatie Laravel Data package.
     *
     * @return Cast The current cast instance
     */
    public function get(): Cast
    {
        return $this;
    }

    /**
     * Casts a numeric value to its floor (next lowest integer).
     *
     * Converts the value to float and applies the floor() function to round down
     * to the nearest integer. Accepts integers, floats, and numeric strings.
     * Non-numeric values are returned unchanged for type safety.
     *
     * @param  DataProperty         $property   The property being cast
     * @param  mixed                $value      The value to cast (expected to be numeric)
     * @param  array<string, mixed> $properties All properties in the data object
     * @param  CreationContext      $context    The creation context for the data object
     * @return mixed                Floor value as float if numeric, otherwise the original value
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_int($value) && !is_float($value) && !is_numeric($value)) {
            return $value;
        }

        return floor((float) $value);
    }
}
