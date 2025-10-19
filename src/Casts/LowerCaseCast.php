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
use function mb_strtolower;

/**
 * Converts string values to lowercase using multibyte-safe string operations.
 *
 * This cast applies lowercase transformation to string properties while preserving
 * non-string values unchanged. Uses mb_strtolower() to correctly handle Unicode
 * characters and international text, making it safe for multilingual applications.
 *
 * ```php
 * use Cline\Data\Casts\LowerCaseCast;
 *
 * final class UserData extends Data
 * {
 *     public function __construct(
 *         #[LowerCaseCast]
 *         public string $email, // "John@Example.COM" becomes "john@example.com"
 *         #[LowerCaseCast]
 *         public string $username, // "MÜLLER" becomes "müller"
 *     ) {}
 * }
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute()]
final class LowerCaseCast implements Cast, GetsCast
{
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
     * Transforms string values to lowercase using multibyte-safe operations.
     *
     * Non-string values are returned unchanged to prevent type coercion issues.
     * This preserves the original data type for numeric, boolean, or null values.
     *
     * @param  DataProperty         $property   The property being cast (unused but required by interface)
     * @param  mixed                $value      The raw value to cast, typically a string but may be any type
     * @param  array<string, mixed> $properties All properties being cast in the current context
     * @param  CreationContext      $context    Metadata about the data object creation process
     * @return mixed                The lowercase string if value is a string, otherwise the original value
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return mb_strtolower($value);
    }
}
