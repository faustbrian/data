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
use function str_replace;

/**
 * Performs string replacement on property values during casting.
 *
 * This cast applies a search-and-replace operation to string properties, replacing
 * all occurrences of a specified substring with a replacement string. Non-string
 * values pass through unchanged. Useful for data normalization, removing unwanted
 * characters, or standardizing format variations in incoming data.
 *
 * ```php
 * use Cline\Data\Casts\ReplaceCast;
 *
 * final class ProductData extends Data
 * {
 *     public function __construct(
 *         #[ReplaceCast(search: '-', replace: '')]
 *         public string $sku, // "ABC-123-XYZ" becomes "ABC123XYZ"
 *         #[ReplaceCast(search: ' ', replace: '_')]
 *         public string $slug, // "Product Name" becomes "Product_Name"
 *         #[ReplaceCast(search: ',', replace: '.')]
 *         public string $decimal, // "1,234.56" becomes "1.234.56"
 *     ) {}
 * }
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class ReplaceCast implements Cast, GetsCast
{
    /**
     * Create a new ReplaceCast instance with search and replacement strings.
     *
     * @param string $search  The substring to search for in the property value. All occurrences
     *                        of this exact string will be replaced. Case-sensitive matching is
     *                        used via str_replace(), so "ABC" will not match "abc".
     * @param string $replace The string to use as a replacement for each occurrence of $search.
     *                        Can be an empty string to effectively remove the search substring.
     *                        Each matched occurrence is replaced independently.
     */
    public function __construct(
        private string $search,
        private string $replace,
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
     * Replaces all occurrences of the search string with the replacement string.
     *
     * Applies str_replace() to perform case-sensitive replacement of all matching
     * substrings. Non-string values are returned unchanged to preserve type safety.
     *
     * @param  DataProperty         $property   The property being cast (unused but required by interface)
     * @param  mixed                $value      The raw value to cast, typically a string but may be any type
     * @param  array<string, mixed> $properties All properties being cast in the current context
     * @param  CreationContext      $context    Metadata about the data object creation process
     * @return mixed                The modified string with replacements applied, or the original value if not a string
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return str_replace($this->search, $this->replace, $value);
    }
}
