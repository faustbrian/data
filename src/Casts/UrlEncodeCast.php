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
use function urlencode;

/**
 * Encodes string values for safe inclusion in URLs by converting special characters to percent-encoding.
 *
 * Applies PHP's urlencode() function to transform strings into URL-safe format (e.g., "Hello World"
 * becomes "Hello+World"). Converts special characters, spaces, and non-alphanumeric characters
 * to percent-encoded representations. Non-string values pass through unchanged. Useful for preparing
 * values for query strings or URL components in HTTP requests.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class UrlEncodeCast implements Cast, GetsCast
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
     * Encode special characters for safe URL inclusion.
     *
     * Applies urlencode() to convert spaces and special characters to percent-encoded
     * format suitable for query strings. Non-string values pass through unchanged
     * to prevent type coercion errors.
     *
     * @param  DataProperty         $property   The property being cast (unused in this implementation)
     * @param  mixed                $value      The value to URL-encode
     * @param  array<string, mixed> $properties All properties in the data object (unused in this implementation)
     * @param  CreationContext      $context    Context information about the data creation process (unused)
     * @return mixed                The URL-encoded string, or the original value if not a string
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return urlencode($value);
    }
}
