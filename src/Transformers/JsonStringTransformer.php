<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Transformers;

use InvalidArgumentException;
use Override;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

use function json_encode;
use function json_last_error_msg;
use function throw_if;

/**
 * Transforms property values into JSON-encoded strings during data object transformation.
 *
 * This transformer serializes complex data structures (arrays, objects, nested data) into
 * JSON strings, enabling storage of structured data in string fields or API responses that
 * require JSON-encoded values. Uses sensible defaults for encoding flags and depth limits.
 *
 * Throws an exception if JSON encoding fails, ensuring data integrity rather than silently
 * producing invalid output. Commonly used for storing metadata, configuration, or nested
 * structures in database string columns.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class JsonStringTransformer implements Transformer
{
    /**
     * Creates a new JSON string transformer with configurable encoding options.
     *
     * @param int $flags JSON encoding options bitmask. Defaults to JSON_UNESCAPED_SLASHES
     *                   and JSON_UNESCAPED_UNICODE to produce clean, readable JSON strings
     *                   without excessive escaping of forward slashes or Unicode characters.
     * @param int $depth Maximum nesting depth for JSON encoding. Defaults to 512 levels,
     *                   which is sufficient for most use cases while preventing infinite
     *                   recursion or extremely deep nesting from causing issues.
     */
    public function __construct(
        private int $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        private int $depth = 512,
    ) {}

    /**
     * Encodes the property value as a JSON string.
     *
     * Attempts to serialize the value to JSON using the configured flags and depth.
     * Throws an exception with the specific JSON encoding error if encoding fails.
     *
     * @param DataProperty          $property Metadata about the property being transformed
     * @param mixed                 $value    The value to encode as JSON (arrays, objects, scalars, etc.)
     * @param TransformationContext $context  Context information about the transformation operation
     *
     * @throws InvalidArgumentException When JSON encoding fails due to invalid data structure or encoding errors
     *
     * @return string JSON-encoded string representation of the value
     */
    #[Override()]
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        $json = json_encode($value, $this->flags, $this->depth); // @phpstan-ignore argument.type (depth is validated as positive int in constructor)

        throw_if($json === false, InvalidArgumentException::class, 'Failed to encode value as JSON: '.json_last_error_msg());

        return $json;
    }
}
