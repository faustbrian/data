<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Types;

use Override;
use Stringable;

use function is_scalar;
use function mb_trim;
use function preg_replace;

/**
 * Normalizes diverse string-like inputs into clean, consistent string values.
 *
 * This type handles common string normalization requirements for APIs, forms, and data
 * sources that produce messy or inconsistent text input. It removes control characters,
 * trims whitespace, and optionally collapses internal whitespace while preserving
 * meaningful content.
 *
 * Capabilities:
 * - Trims leading/trailing multibyte whitespace
 * - Removes control characters (except tabs, newlines, carriage returns)
 * - Optionally collapses consecutive whitespace to single spaces
 * - Optionally treats empty strings as null for cleaner data handling
 * - Accepts strings, scalars, and Stringable objects
 *
 * ```php
 * StringLike::create('  hello  ')->value();              // "hello"
 * StringLike::create("hello\x00world")->value();         // "helloworld"
 * StringLike::create('hello   world', collapseWhitespace: true)->value(); // "hello world"
 * StringLike::create('   ', blankAsNull: true)->value(); // null
 * StringLike::create(123)->value();                      // "123"
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class StringLike implements Stringable
{
    /**
     * Creates a new StringLike instance with the normalized string value.
     *
     * @param null|string $value the normalized string value after trimming, control character
     *                           removal, and optional whitespace collapse, or null if the
     *                           input was empty/blank and blankAsNull is enabled
     */
    private function __construct(
        private ?string $value,
    ) {}

    /**
     * Returns the string representation for string casting operations.
     *
     * Enables StringLike objects to be used in string contexts seamlessly.
     * Returns an empty string when the normalized value is null.
     *
     * @return string The normalized string value, or empty string if null
     */
    #[Override()]
    public function __toString(): string
    {
        return $this->value ?? '';
    }

    /**
     * Creates a StringLike instance from diverse input types with configurable normalization.
     *
     * Attempts to convert the input to a normalized string using multiple strategies:
     * - Accepts strings, scalars (int, float, bool), and Stringable objects
     * - Removes control characters except tabs, newlines, and carriage returns
     * - Always trims leading/trailing whitespace
     * - Optionally collapses consecutive whitespace characters to single spaces
     * - Optionally converts blank strings to null for cleaner data handling
     *
     * @param  mixed $value              The input value to normalize into a string
     * @param  bool  $blankAsNull        Whether to treat empty strings as null after normalization.
     *                                   Defaults to true, which helps prevent "empty but not null"
     *                                   pollution in data objects.
     * @param  bool  $collapseWhitespace Whether to collapse consecutive whitespace characters
     *                                   (spaces, tabs, newlines) into single spaces. Defaults
     *                                   to false, preserving original spacing and line breaks.
     * @return self  Immutable StringLike instance with the normalized string or null
     */
    public static function create(mixed $value, bool $blankAsNull = true, bool $collapseWhitespace = false): self
    {
        if ($value === null) {
            return new self(null);
        }

        if (!is_scalar($value) && !($value instanceof Stringable)) {
            return new self(null);
        }

        $s = (string) $value;

        // Remove control characters, but preserve tabs (\t), newlines (\n) and carriage returns (\r)
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $s) ?? $s;

        // Trim first (always trim edges)
        $s = mb_trim($s);

        // Collapse whitespace if requested (after trimming)
        if ($collapseWhitespace) {
            $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        }

        if ($blankAsNull && $s === '') {
            return new self(null);
        }

        return new self($s);
    }

    /**
     * Returns the normalized string value or null if empty.
     *
     * @return null|string The normalized string after trimming, control character removal,
     *                     and optional whitespace collapse, or null if the value is empty
     */
    public function value(): ?string
    {
        return $this->value;
    }

    /**
     * Checks if the normalized value is empty or null.
     *
     * @return bool True if the value is null or an empty string, false otherwise
     */
    public function isEmpty(): bool
    {
        return $this->value === null || $this->value === '';
    }
}
