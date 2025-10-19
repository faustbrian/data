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

use function array_keys;
use function is_array;

/**
 * Extracts the keys from an associative array or returns the original value.
 *
 * Transforms an associative array into a numerically indexed array containing
 * only the keys from the original array. Non-array values are passed through
 * unchanged, allowing the cast to gracefully handle edge cases without throwing
 * exceptions.
 *
 * @deprecated Use ArrayToKeysCoercer instead
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute()]
final class ArrayToKeysCast implements Cast, GetsCast
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
     * Casts an array value to a list of its keys.
     *
     * Extracts all keys from the provided array using array_keys(). If the value
     * is not an array, it is returned unchanged to maintain compatibility with
     * optional or nullable properties.
     *
     * @param  DataProperty         $property   The property being cast
     * @param  mixed                $value      The value to cast (expected to be an array)
     * @param  array<string, mixed> $properties All properties in the data object
     * @param  CreationContext      $context    The creation context for the data object
     * @return mixed                Array of keys if value is an array, otherwise the original value
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        return array_keys($value);
    }
}
