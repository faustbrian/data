<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Transformers;

use Override;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

use function is_string;
use function mb_strtolower;

/**
 * Transforms string property values to lowercase during data object transformation.
 *
 * This transformer converts string values to lowercase using multibyte-safe functions,
 * ensuring proper handling of Unicode characters and international alphabets. Non-string
 * values pass through unchanged, making it safe to apply to mixed-type properties.
 *
 * Commonly used for normalizing user input, email addresses, usernames, or any text
 * that should be case-insensitive in storage or API responses.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class LowerCaseTransformer implements Transformer
{
    /**
     * Converts string values to lowercase while preserving non-string values.
     *
     * Uses multibyte string functions to ensure correct lowercasing of Unicode
     * characters beyond the ASCII range, making it suitable for international text.
     *
     * @param  DataProperty          $property Metadata about the property being transformed
     * @param  mixed                 $value    The value to transform (strings are lowercased, other types unchanged)
     * @param  TransformationContext $context  Context information about the transformation operation
     * @return mixed                 Lowercased string if input is a string, otherwise the original value
     */
    #[Override()]
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return mb_strtolower($value);
    }
}
