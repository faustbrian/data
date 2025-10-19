<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Casts;

use Attribute;
use Cline\Data\Types\NumberLike;
use InvalidArgumentException;
use Override;
use Spatie\LaravelData\Attributes\GetsCast;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

use function in_array;
use function throw_unless;

/**
 * Normalizes and converts number-like values to a specified output format.
 *
 * This cast processes various number representations (strings with separators, decimals,
 * scientific notation) through the NumberLike type to produce clean, normalized output
 * in the desired format. Handles international number formats, removes formatting
 * characters, and ensures consistent numeric representation.
 *
 * ```php
 * use Cline\Data\Casts\NumberLikeCast;
 *
 * final class ProductData extends Data
 * {
 *     public function __construct(
 *         #[NumberLikeCast(as: 'float')]
 *         public float $price, // "1,234.56" becomes 1234.56
 *         #[NumberLikeCast(as: 'int')]
 *         public int $quantity, // "1,000" becomes 1000
 *         #[NumberLikeCast(as: 'string')]
 *         public string $sku, // Preserves normalized number as string
 *     ) {}
 * }
 * ```
 *
 * @psalm-type Output = 'string'|'int'|'float'
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute()]
final readonly class NumberLikeCast implements Cast, GetsCast
{
    /**
     * Create a new NumberLikeCast instance with the specified output format.
     *
     * @param 'float'|'int'|'string' $as The desired output format for the normalized number.
     *                                   Determines whether the NumberLike value is returned
     *                                   as a string representation, integer, or float value.
     *                                   Defaults to 'string' to preserve precision and avoid
     *                                   unintended type coercion in business logic.
     *
     * @throws InvalidArgumentException When $as is not one of the allowed values
     */
    public function __construct(
        private string $as = 'string',
    ) {
        throw_unless(in_array($as, ['string', 'int', 'float'], true), InvalidArgumentException::class, 'NumberLikeCast: $as must be one of string|int|float');
    }

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
     * Normalizes the input value and converts it to the specified output format.
     *
     * Processes the value through NumberLike to strip formatting characters, handle
     * international number formats, and convert to a clean numeric representation.
     * The output type depends on the $as constructor parameter.
     *
     * @param  DataProperty          $property   The property being cast (unused but required by interface)
     * @param  mixed                 $value      The raw value to normalize, can be string with formatting or numeric
     * @param  array<string, mixed>  $properties All properties being cast in the current context
     * @param  CreationContext       $context    Metadata about the data object creation process
     * @return null|float|int|string The normalized number in the format specified by $as parameter, or null if conversion fails
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        $n = NumberLike::create($value);

        if ($this->as === 'string') {
            return $n->value();
        }

        if ($this->as === 'int') {
            return $n->asInt();
        }

        return $n->asFloat();
    }
}
