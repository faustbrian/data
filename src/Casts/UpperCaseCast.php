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

use function is_string;
use function mb_strtoupper;

/**
 * Converts string values to uppercase using multibyte-safe case conversion.
 *
 * Applies PHP's mb_strtoupper() function to transform all characters in strings
 * to uppercase. Handles multibyte characters correctly for international text,
 * ensuring proper case conversion for accented letters and non-Latin scripts.
 * Non-string values pass through unchanged. Useful for normalizing identifiers,
 * codes, or display text that should always appear in uppercase.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class UpperCaseCast implements Cast, GetsCast
{
    /**
     * Retrieve the cast instance for application to data properties.
     *
     * @return Cast The current cast instance ready for value transformation
     */
    public function get(): Cast
    {
        return $this;
    }

    /**
     * Convert the property value to uppercase.
     *
     * Applies multibyte-safe uppercase conversion using mb_strtoupper().
     * Non-string values pass through unchanged to prevent type coercion errors.
     *
     * @param  DataProperty         $property   The property being cast (unused in this implementation)
     * @param  mixed                $value      The value to convert to uppercase
     * @param  array<string, mixed> $properties All properties in the data object (unused in this implementation)
     * @param  CreationContext      $context    Context information about the data creation process (unused)
     * @return mixed                The uppercased string, or the original value if not a string
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return mb_strtoupper($value);
    }
}
