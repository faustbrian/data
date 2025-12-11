<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Types;

use LogicException;

use function in_array;
use function is_bool;
use function is_numeric;
use function is_string;
use function mb_strtolower;
use function mb_trim;
use function throw_if;

/**
 * Normalizes diverse truthy and falsy representations into a tri-state boolean value.
 *
 * This type handles the common problem of inconsistent boolean representations across
 * APIs, form inputs, and configuration sources. It recognizes multiple string patterns
 * for true/false values and supports an explicit "unknown" state for ambiguous inputs,
 * preventing silent coercion errors.
 *
 * Supports three states: true, false, and unknown (null). The unknown state represents
 * inputs that cannot be confidently interpreted as boolean, such as arbitrary strings
 * or numeric values other than 0 and 1.
 *
 * ```php
 * BooleanLike::create('yes')->isTrue();     // true
 * BooleanLike::create('0')->isFalse();      // true
 * BooleanLike::create(null)->isUnknown();   // true
 * BooleanLike::create('maybe')->isUnknown(); // true
 * BooleanLike::create('yes')->value();      // true
 * BooleanLike::create('maybe')->orDefault(false); // false
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class BooleanLike
{
    /**
     * Default patterns recognized as truthy values.
     */
    public const array DEFAULT_TRUTHY = ['1', 'true', 'yes', 'y', 'on'];

    /**
     * Default patterns recognized as falsy values.
     */
    public const array DEFAULT_FALSY = ['0', 'false', 'no', 'n', 'off'];

    /**
     * Creates a new BooleanLike instance with the resolved boolean value and unknown state.
     *
     * @param bool $value   The resolved boolean value (true or false)
     * @param bool $unknown Whether the input was ambiguous and could not be confidently
     *                      interpreted as a boolean. When true, the value parameter is
     *                      not meaningful and should not be accessed directly.
     */
    private function __construct(
        private bool $value,
        private bool $unknown = false,
    ) {}

    /**
     * Creates a BooleanLike instance from diverse input types with customizable patterns.
     *
     * Attempts to interpret the input as a boolean using multiple strategies:
     * - Native boolean values pass through unchanged
     * - Numeric 1/0 map to true/false; other numbers are unknown
     * - String values are normalized (trimmed, lowercased) and matched against truthy/falsy patterns
     * - null and unrecognized inputs become unknown
     *
     * @param  mixed              $value  The input value to normalize into a boolean-like state
     * @param  array<int, string> $truthy Lowercase string patterns to recognize as true.
     *                                    Defaults to common truthy representations.
     * @param  array<int, string> $falsy  Lowercase string patterns to recognize as false.
     *                                    Defaults to common falsy representations.
     * @return self               Immutable BooleanLike instance representing the interpreted boolean state
     */
    public static function create(mixed $value, array $truthy = self::DEFAULT_TRUTHY, array $falsy = self::DEFAULT_FALSY): self
    {
        if ($value === null) {
            return new self(false, unknown: true);
        }

        if (is_bool($value)) {
            return new self($value);
        }

        if (is_numeric($value)) {
            // Only 1 and 0 map strictly; everything else is unknown
            if ((string) $value === '1') {
                return new self(true);
            }

            if ((string) $value === '0') {
                return new self(false);
            }

            return new self(false, unknown: true);
        }

        if (is_string($value)) {
            $s = mb_strtolower(mb_trim($value));

            if (in_array($s, $truthy, true)) {
                return new self(true);
            }

            if (in_array($s, $falsy, true)) {
                return new self(false);
            }
        }

        return new self(false, unknown: true);
    }

    /**
     * Checks if the normalized value represents true.
     *
     * @return bool True if the value was successfully interpreted as truthy, false otherwise
     */
    public function isTrue(): bool
    {
        return $this->value;
    }

    /**
     * Checks if the normalized value represents false.
     *
     * @return bool True if the value was successfully interpreted as falsy, false otherwise
     */
    public function isFalse(): bool
    {
        return $this->value === false;
    }

    /**
     * Checks if the input value could not be confidently interpreted as a boolean.
     *
     * @return bool True if the value is ambiguous or cannot be mapped to true/false
     */
    public function isUnknown(): bool
    {
        return $this->unknown;
    }

    /**
     * Returns the boolean value, throwing an exception if the state is unknown.
     *
     * Use this method when you need a definitive boolean value and want to fail
     * fast on ambiguous inputs rather than silently coercing to a default.
     *
     * @throws LogicException When called on an unknown boolean state. Check isUnknown()
     *                        first or use orDefault() to provide a fallback value.
     *
     * @return bool The resolved boolean value (true or false)
     */
    public function value(): bool
    {
        throw_if($this->unknown, LogicException::class, 'Cannot get value of unknown boolean. Check isUnknown() first or use orDefault().');

        return $this->value;
    }

    /**
     * Returns the boolean value or a default if the state is unknown.
     *
     * Provides safe access to the boolean value with a fallback for ambiguous inputs,
     * preventing exceptions while maintaining control over the default behavior.
     *
     * @param  bool $default The value to return when the boolean state is unknown
     * @return bool The resolved boolean value if known, otherwise the provided default
     */
    public function orDefault(bool $default): bool
    {
        return $this->unknown ? $default : $this->value;
    }
}
