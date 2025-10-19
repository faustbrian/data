<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Casts;

use Attribute;
use Cline\Data\Types\StringLike;
use Override;
use Spatie\LaravelData\Attributes\GetsCast;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

/**
 * Normalizes various input types into clean string values using StringLike transformation.
 *
 * Converts diverse input types (numbers, booleans, stringable objects) into normalized
 * strings with optional whitespace collapsing and blank-to-null conversion. Provides
 * consistent string handling across data objects, ensuring clean text values regardless
 * of input source or format.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute()]
final readonly class StringLikeCast implements Cast, GetsCast
{
    /**
     * Create a new string normalization cast instance.
     *
     * @param bool $blankAsNull        When true, converts empty strings and whitespace-only strings to null
     *                                 for consistent null handling. When false, preserves blank strings as-is.
     *                                 Defaults to true for cleaner data representation.
     * @param bool $collapseWhitespace When true, collapses multiple consecutive whitespace characters
     *                                 into single spaces and trims leading/trailing whitespace.
     *                                 When false, preserves original whitespace patterns. Defaults to false
     *                                 to maintain input formatting unless explicitly normalized.
     */
    public function __construct(
        private bool $blankAsNull = true,
        private bool $collapseWhitespace = false,
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
     * Transform the property value into a normalized string or null.
     *
     * Applies StringLike normalization which converts various types (integers, floats,
     * booleans, stringable objects) into string representation, then applies configured
     * whitespace and blank handling rules to produce clean, consistent string values.
     *
     * @param  DataProperty         $property   The property being cast (unused in this implementation)
     * @param  mixed                $value      The value to normalize into a string
     * @param  array<string, mixed> $properties All properties in the data object (unused in this implementation)
     * @param  CreationContext      $context    Context information about the data creation process (unused)
     * @return null|string          The normalized string value, or null if blank and blankAsNull is enabled
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return StringLike::create($value, $this->blankAsNull, $this->collapseWhitespace)->value();
    }
}
