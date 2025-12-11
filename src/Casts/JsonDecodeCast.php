<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Casts;

use Attribute;
use JsonException;
use Override;
use Spatie\LaravelData\Attributes\GetsCast;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

use const JSON_THROW_ON_ERROR;

use function is_string;
use function json_decode;

/**
 * Decodes JSON strings into PHP arrays or objects with configurable options.
 *
 * Provides flexible JSON parsing with control over output format (associative arrays
 * vs objects), nesting depth limits, and error handling behavior. By default, returns
 * associative arrays and throws exceptions on malformed JSON for strict validation.
 * Non-string values are passed through unchanged.
 *
 * @author Brian Faust <brian@cline.sh>
 * @deprecated Use JsonDecodeCoercer instead
 *
 * @psalm-immutable
 */
#[Attribute()]
final readonly class JsonDecodeCast implements Cast, GetsCast
{
    /**
     * Create a new JSON decode cast instance.
     *
     * @param bool $associative Whether to return associative arrays (true) or objects (false).
     *                          When true, converts JSON objects to PHP associative arrays.
     *                          When false, returns stdClass objects for JSON objects.
     * @param int  $depth       Maximum nesting depth for JSON structure parsing. Prevents
     *                          stack overflow attacks and excessive memory usage from deeply
     *                          nested JSON. Defaults to 512 levels, which is sufficient for
     *                          most legitimate use cases while providing protection.
     * @param int  $flags       JSON decoding flags as bitmask. Defaults to JSON_THROW_ON_ERROR
     *                          which throws JsonException on invalid JSON instead of returning
     *                          null, enabling proper error handling and validation.
     */
    public function __construct(
        private bool $associative = true,
        private int $depth = 512,
        private int $flags = JSON_THROW_ON_ERROR,
    ) {}

    /**
     * Returns the cast instance for the Spatie Laravel Data package.
     *
     * @return Cast The current cast instance
     */
    public function get(): Cast
    {
        return $this;
    }

    /**
     * Casts a JSON string to a PHP array or object.
     *
     * Parses a JSON-encoded string using the configured options for output format,
     * depth limits, and error handling. Non-string values are returned unchanged
     * for compatibility with optional properties.
     *
     * @param DataProperty         $property   The property being cast
     * @param mixed                $value      The value to cast (expected to be a JSON string)
     * @param array<string, mixed> $properties All properties in the data object
     * @param CreationContext      $context    The creation context for the data object
     *
     * @throws JsonException When JSON decoding fails and JSON_THROW_ON_ERROR flag is set
     *
     * @return mixed Decoded JSON as array/object if value is a string, otherwise the original value
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return json_decode($value, associative: $this->associative, depth: $this->depth, flags: $this->flags);
    }
}
