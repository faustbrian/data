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
use function mb_trim;

/**
 * Removes leading and trailing characters from string values using multibyte-safe trimming.
 *
 * Applies PHP's mb_trim() function to strip specified characters from both ends of strings.
 * Defaults to removing standard whitespace characters (spaces, tabs, newlines, carriage returns,
 * null bytes, and vertical tabs). Handles multibyte characters correctly for international text.
 * Non-string values pass through unchanged. Useful for cleaning user input and normalizing text data.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class TrimCast implements Cast, GetsCast
{
    /**
     * Create a new trim cast instance.
     *
     * @param string $characters Character mask specifying which characters to strip from string ends.
     *                           Defaults to standard whitespace: space, tab (\t), newline (\n),
     *                           carriage return (\r), null byte (\0), and vertical tab (\x0B).
     *                           Can be customized to trim specific characters like punctuation or symbols.
     */
    public function __construct(
        private string $characters = " \t\n\r\0\x0B",
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
     * Trim leading and trailing characters from the property value.
     *
     * Applies multibyte-safe trimming using mb_trim() with the configured character mask.
     * Non-string values pass through unchanged to prevent type coercion errors.
     *
     * @param  DataProperty         $property   The property being cast (unused in this implementation)
     * @param  mixed                $value      The value to trim
     * @param  array<string, mixed> $properties All properties in the data object (unused in this implementation)
     * @param  CreationContext      $context    Context information about the data creation process (unused)
     * @return mixed                The trimmed string, or the original value if not a string
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return mb_trim($value, $this->characters);
    }
}
