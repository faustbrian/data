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

use function array_values;
use function is_array;

/**
 * Converts an associative array to a list with sequential numeric keys.
 *
 * Transforms any array into a list by extracting only the values and reindexing
 * them with sequential numeric keys starting from 0. This is useful for normalizing
 * associative arrays or arrays with non-sequential keys into standard lists.
 * Non-array values are passed through unchanged.
 *
 * @deprecated Use ArrayToListCoercer instead
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute()]
final class ArrayToListCast implements Cast, GetsCast
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
     * Casts an array value to a numerically indexed list of values.
     *
     * Extracts all values from the provided array using array_values() and reindexes
     * them sequentially from 0. If the value is not an array, it is returned unchanged
     * to maintain compatibility with optional or nullable properties.
     *
     * @param  DataProperty         $property   The property being cast
     * @param  mixed                $value      The value to cast (expected to be an array)
     * @param  array<string, mixed> $properties All properties in the data object
     * @param  CreationContext      $context    The creation context for the data object
     * @return mixed                List of values if value is an array, otherwise the original value
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        return array_values($value);
    }
}
