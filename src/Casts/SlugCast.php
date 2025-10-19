<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Casts;

use Attribute;
use Illuminate\Support\Str;
use Override;
use Spatie\LaravelData\Attributes\GetsCast;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

use function is_string;

/**
 * Transforms string values into URL-friendly slugs using Laravel's Str::slug() helper.
 *
 * Converts strings to lowercase, replaces non-alphanumeric characters with separators,
 * and handles language-specific transliteration rules. Non-string values pass through
 * unchanged. Useful for generating URL slugs from titles or names in data objects.
 *
 * @deprecated This cast provides no logic and can be removed
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class SlugCast implements Cast, GetsCast
{
    /**
     * Create a new slug cast instance.
     *
     * @param string                $separator  Character used to separate words in the slug (typically '-' or '_').
     *                                          Defaults to hyphen for standard URL-friendly slugs.
     * @param null|string           $language   Language code for language-specific transliteration rules (e.g., 'de', 'fr').
     *                                          When null, uses default ASCII transliteration rules.
     * @param array<string, string> $dictionary Custom character replacement mappings applied before slug generation.
     *                                          Useful for preserving or replacing specific characters or sequences.
     */
    public function __construct(
        private string $separator = '-',
        private ?string $language = null,
        private array $dictionary = [],
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
     * Transform the property value into a URL-friendly slug.
     *
     * Applies Laravel's Str::slug() transformation with configured separator, language,
     * and dictionary settings. Non-string values pass through unchanged to prevent
     * type coercion errors.
     *
     * @param  DataProperty         $property   The property being cast (unused in this implementation)
     * @param  mixed                $value      The value to transform into a slug
     * @param  array<string, mixed> $properties All properties in the data object (unused in this implementation)
     * @param  CreationContext      $context    Context information about the data creation process (unused)
     * @return mixed                The slugified string, or the original value if not a string
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return Str::slug(title: $value, separator: $this->separator, language: $this->language, dictionary: $this->dictionary);
    }
}
