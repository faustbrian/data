<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Casts;

use Attribute;
use InvalidArgumentException;
use Override;
use Spatie\LaravelData\Attributes\GetsCast;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

use const FILTER_SANITIZE_STRING;

use function filter_var;
use function is_string;
use function throw_if;

/**
 * Sanitizes string values using PHP's filter_var() with configurable filters.
 *
 * This cast applies PHP's filtering functions to clean and sanitize string input,
 * removing or encoding potentially dangerous characters. Supports all PHP filter
 * constants (FILTER_SANITIZE_*) and flags. Throws an exception if sanitization fails,
 * ensuring data integrity. Non-string values pass through unchanged.
 *
 * ```php
 * use Cline\Data\Casts\SanitizeCast;
 *
 * final class CommentData extends Data
 * {
 *     public function __construct(
 *         #[SanitizeCast(filter: FILTER_SANITIZE_STRING)]
 *         public string $content, // Strips HTML tags from user input
 *         #[SanitizeCast(filter: FILTER_SANITIZE_EMAIL)]
 *         public string $email, // Removes illegal characters from email
 *         #[SanitizeCast(filter: FILTER_SANITIZE_URL)]
 *         public string $website, // Sanitizes URL by removing illegal characters
 *     ) {}
 * }
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 * @deprecated FILTER_SANITIZE_STRING is deprecated as of PHP 8.1. This cast should be
 *             replaced with explicit HTML purification libraries (e.g., HTML Purifier)
 *             or validation casts. Consider using validation instead of sanitization
 *             for security-sensitive data to avoid silent data corruption.
 *
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class SanitizeCast implements Cast, GetsCast
{
    /**
     * Create a new SanitizeCast instance with filter type and flags.
     *
     * @param int $filter PHP filter constant specifying the sanitization type to apply.
     *                    Common values: FILTER_SANITIZE_STRING (deprecated in PHP 8.1),
     *                    FILTER_SANITIZE_EMAIL, FILTER_SANITIZE_URL, FILTER_SANITIZE_NUMBER_INT,
     *                    FILTER_SANITIZE_NUMBER_FLOAT. Note that FILTER_SANITIZE_STRING is
     *                    deprecated and should not be used in PHP 8.1+.
     * @param int $flags  Optional filter flags that modify sanitization behavior. Flags are
     *                    filter-specific and combine using bitwise OR. For example,
     *                    FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH for string filters,
     *                    or FILTER_FLAG_ALLOW_FRACTION for number filters. Defaults to 0 (no flags).
     */
    public function __construct(
        private int $filter = FILTER_SANITIZE_STRING,
        private int $flags = 0,
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
     * Sanitizes string values using the configured filter and flags.
     *
     * Applies filter_var() to remove or encode unwanted characters based on the
     * specified filter type. Throws an exception if the filter operation fails,
     * preventing potentially unsafe data from entering the application.
     *
     * @param DataProperty         $property   The property being cast (unused but required by interface)
     * @param mixed                $value      The raw value to sanitize, typically a string but may be any type
     * @param array<string, mixed> $properties All properties being cast in the current context
     * @param CreationContext      $context    Metadata about the data object creation process
     *
     * @throws InvalidArgumentException When the filter operation fails (filter_var returns false)
     *
     * @return mixed The sanitized string if successful, or the original value if not a string
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $result = filter_var($value, $this->filter, $this->flags);

        throw_if($result === false, InvalidArgumentException::class, 'Failed to sanitize value');

        return $result;
    }
}
