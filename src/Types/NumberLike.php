<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Types;

use function is_float;
use function is_int;
use function is_string;
use function mb_rtrim;
use function mb_trim;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_contains;
use function str_replace;

/**
 * Normalizes diverse numeric representations into a consistent, lossless format.
 *
 * This type handles the common problem of inconsistent numeric formats across APIs,
 * locales, and data sources. It recognizes various thousand separators, decimal markers,
 * and whitespace patterns while preserving precision by storing values as canonical
 * strings rather than native floats.
 *
 * Accepts integers, floats, and numeric strings with:
 * - Comma or dot as decimal separator (context-aware detection)
 * - Various thousand separators (comma, space, NBSP, thin space)
 * - Leading/trailing whitespace
 * - Optional sign (+/-)
 *
 * Stores the normalized value as a string to avoid floating-point precision loss,
 * making it suitable for financial calculations and precise decimal arithmetic.
 *
 * ```php
 * NumberLike::create(1234.56)->value();        // "1234.56"
 * NumberLike::create('1,234.56')->asFloat();   // 1234.56
 * NumberLike::create('1.234,56')->asFloat();   // 1234.56 (European format)
 * NumberLike::create('1 234.56')->asInt();     // 1234
 * NumberLike::create('')->value();             // null
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class NumberLike
{
    /**
     * Creates a new NumberLike instance with the normalized numeric string.
     *
     * @param null|string $raw The canonical numeric string representation (sign + digits + optional decimal),
     *                         or null if the input could not be parsed as a valid number. Negative
     *                         zero is normalized to zero for consistency.
     */
    private function __construct(
        private ?string $raw,
    ) {}

    /**
     * Creates a NumberLike instance from diverse input types and formats.
     *
     * Attempts to parse the input as a number using multiple strategies:
     * - Native integers and floats are converted to canonical string form
     * - String values are normalized by removing thousand separators and detecting decimal markers
     * - Empty strings and unparseable values return null
     * - Ambiguous comma/dot usage is resolved by context (both present vs. one present)
     *
     * @param  mixed $value The input value to normalize into a numeric representation
     * @return self  Immutable NumberLike instance with canonical string representation or null
     */
    public static function create(mixed $value): self
    {
        if ($value === null) {
            return new self(null);
        }

        if (is_int($value)) {
            return new self((string) $value);
        }

        if (is_float($value)) {
            return new self(self::floatToCanonicalString($value));
        }

        if (is_string($value)) {
            $s = mb_trim($value);

            if ($s === '') {
                return new self(null);
            }

            // Remove spaces and NBSP (common thousand separators)
            $s = str_replace(["\xC2\xA0", ' '], '', $s);

            $hasComma = str_contains($s, ',');
            $hasDot = str_contains($s, '.');

            if ($hasComma && $hasDot) {
                // Assume comma used as thousands separator
                $s = str_replace(',', '', $s);
            } elseif ($hasComma) {
                // Treat comma as decimal separator
                $s = str_replace(',', '.', $s);
            }

            // Remove other thin/space separators that may appear
            $s = preg_replace('/[\x{2000}-\x{200A}\x{202F}]/u', '', $s) ?? $s;

            if (preg_match('/^[+-]?\d+(\.\d+)?$/', $s) !== 1) {
                return new self(null);
            }

            // Normalize negative zero to zero
            if ($s === '-0') {
                $s = '0';
            }

            return new self($s);
        }

        return new self(null);
    }

    /**
     * Returns the canonical numeric string representation.
     *
     * The canonical form uses dot as decimal separator, no thousand separators,
     * and removes trailing zeros. Returns null if the original input was not
     * parseable as a valid number.
     *
     * @return null|string Normalized numeric string (e.g., "1234.56", "-0.5", "42"), or null if invalid
     */
    public function value(): ?string
    {
        return $this->raw;
    }

    /**
     * Checks if the normalized value represents an integer (no decimal component).
     *
     * @return bool True if the value is a whole number without decimals, false otherwise
     */
    public function isInteger(): bool
    {
        return $this->raw !== null && !str_contains($this->raw, '.');
    }

    /**
     * Checks if the normalized value contains a decimal component.
     *
     * @return bool True if the value has a fractional part, false otherwise
     */
    public function isFloat(): bool
    {
        return $this->raw !== null && str_contains($this->raw, '.');
    }

    /**
     * Converts the normalized value to a native PHP integer.
     *
     * Truncates any decimal portion by casting through float, which preserves
     * the sign and handles the conversion consistently. Returns null if the
     * original input was not parseable.
     *
     * @return null|int Integer representation, or null if value is not parseable
     */
    public function asInt(): ?int
    {
        if ($this->raw === null) {
            return null;
        }

        // Casting via float preserves sign and truncates decimals
        return (int) (float) $this->raw;
    }

    /**
     * Converts the normalized value to a native PHP float.
     *
     * Useful for mathematical operations but may introduce floating-point
     * precision errors. Prefer using the string value() for exact decimal
     * arithmetic or financial calculations.
     *
     * @return null|float Float representation, or null if value is not parseable
     */
    public function asFloat(): ?float
    {
        if ($this->raw === null) {
            return null;
        }

        return (float) $this->raw;
    }

    /**
     * Converts a native float to canonical string representation without precision loss.
     *
     * Uses high-precision formatting and removes trailing zeros to produce a clean
     * string representation. Normalizes negative zero to positive zero for consistency.
     *
     * @param  float  $value The float value to convert to canonical string form
     * @return string Canonical string representation without trailing zeros or unnecessary decimals
     */
    private static function floatToCanonicalString(float $value): string
    {
        // Convert to non-scientific, trim trailing zeros and dot
        $s = sprintf('%.14F', $value);
        $s = mb_rtrim($s, '0');
        $s = mb_rtrim($s, '.');

        if ($s === '-0') {
            return '0';
        }

        return $s;
    }
}
