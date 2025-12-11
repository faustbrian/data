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
use function urldecode;

/**
 * Decodes URL-encoded strings by converting percent-encoded characters back to their original form.
 *
 * Applies PHP's urldecode() function to transform URL-encoded strings (e.g., "Hello%20World"
 * becomes "Hello World"). Converts percent-encoded characters and plus signs back to spaces
 * and original characters. Non-string values pass through unchanged. Useful for processing
 * query parameters or URL components received from HTTP requests.
 *
 * @author Brian Faust <brian@cline.sh>
 * @deprecated Use UrlDecodeCoercer instead
 *
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class UrlDecodeCast implements Cast, GetsCast
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
     * Decode URL-encoded characters in the property value.
     *
     * Applies urldecode() to convert percent-encoded characters and plus signs
     * back to their original form. Non-string values pass through unchanged
     * to prevent type coercion errors.
     *
     * @param  DataProperty         $property   The property being cast (unused in this implementation)
     * @param  mixed                $value      The URL-encoded value to decode
     * @param  array<string, mixed> $properties All properties in the data object (unused in this implementation)
     * @param  CreationContext      $context    Context information about the data creation process (unused)
     * @return mixed                The decoded string, or the original value if not a string
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return urldecode($value);
    }
}
