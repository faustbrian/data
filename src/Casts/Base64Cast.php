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

use function base64_decode;
use function base64_encode;
use function is_string;
use function throw_if;

/**
 * Encodes or decodes base64 strings with configurable validation strictness.
 *
 * Provides bidirectional base64 transformation for string values. By default,
 * decodes base64-encoded strings with strict validation to ensure proper encoding.
 * Can also be configured to encode plain strings to base64 format. Non-string
 * values are passed through unchanged.
 *
 * @author Brian Faust <brian@cline.sh>
 * @deprecated Use Base64DecodeCoercer instead
 *
 * @psalm-immutable
 */
#[Attribute()]
final readonly class Base64Cast implements Cast, GetsCast
{
    /**
     * Create a new base64 cast instance.
     *
     * @param bool $decode Whether to decode (true) or encode (false) the value.
     *                     Defaults to true for decoding base64 strings.
     * @param bool $strict Whether to use strict decoding that rejects invalid
     *                     base64 characters. When true, throws an exception if
     *                     the input contains non-base64 characters. When false,
     *                     silently ignores invalid characters during decoding.
     *                     Only applies when $decode is true.
     */
    public function __construct(
        private bool $decode = true,
        private bool $strict = true,
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
     * Casts a string value to or from base64 encoding.
     *
     * When decoding mode is enabled, decodes a base64-encoded string to its original
     * form. When encoding mode is enabled, encodes a plain string to base64 format.
     * Non-string values are returned unchanged for compatibility with optional properties.
     *
     * @param DataProperty         $property   The property being cast
     * @param mixed                $value      The value to cast (expected to be a string)
     * @param array<string, mixed> $properties All properties in the data object
     * @param CreationContext      $context    The creation context for the data object
     *
     * @throws InvalidArgumentException When decoding fails in strict mode due to invalid base64
     *
     * @return mixed Decoded/encoded string if value is a string, otherwise the original value
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if ($this->decode) {
            $decoded = base64_decode($value, $this->strict);

            throw_if($decoded === false, InvalidArgumentException::class, 'Invalid base64 string');

            return $decoded;
        }

        return base64_encode($value);
    }
}
