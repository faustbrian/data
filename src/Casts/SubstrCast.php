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
use function mb_substr;

/**
 * Extracts a substring from string values using multibyte-safe substring operations.
 *
 * Applies PHP's mb_substr() function to extract portions of strings based on start
 * position and optional length parameters. Handles multibyte characters correctly
 * for international text. Non-string values pass through unchanged. Useful for
 * truncating text, extracting prefixes/suffixes, or limiting string lengths in data objects.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class SubstrCast implements Cast, GetsCast
{
    /**
     * Create a new substring extraction cast instance.
     *
     * @param int      $start  Starting position for substring extraction. Positive values start from
     *                         the beginning (0-indexed), negative values count back from the end.
     *                         For example, 0 starts at the first character, -3 starts three characters
     *                         from the end.
     * @param null|int $length Maximum length of the extracted substring in characters. When null,
     *                         extracts from start position to the end of the string. Positive values
     *                         specify character count, negative values exclude characters from the end.
     */
    public function __construct(
        private int $start,
        private ?int $length = null,
    ) {}

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
     * Extract a substring from the property value.
     *
     * Applies multibyte-safe substring extraction using mb_substr() with configured
     * start position and optional length. Non-string values pass through unchanged
     * to prevent type coercion errors.
     *
     * @param  DataProperty         $property   The property being cast (unused in this implementation)
     * @param  mixed                $value      The value to extract a substring from
     * @param  array<string, mixed> $properties All properties in the data object (unused in this implementation)
     * @param  CreationContext      $context    Context information about the data creation process (unused)
     * @return mixed                The extracted substring, or the original value if not a string
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return $this->length === null
            ? mb_substr($value, $this->start)
            : mb_substr($value, $this->start, $this->length);
    }
}
