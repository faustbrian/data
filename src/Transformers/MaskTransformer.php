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
use function mb_strlen;
use function mb_substr;
use function str_repeat;

/**
 * Masks sensitive string values by replacing characters with a configurable mask character.
 *
 * This transformer protects sensitive data (credit cards, phone numbers, emails, passwords)
 * by replacing the middle portion of strings with mask characters while preserving a
 * configurable number of characters at the start and end for identification purposes.
 *
 * Uses multibyte-safe functions to correctly handle Unicode characters. If the string is
 * shorter than the combined visible character count, the entire string is masked to prevent
 * information leakage.
 *
 * ```php
 * // Example: Mask credit card, showing last 4 digits
 * #[WithTransformer(MaskTransformer::class, mask: '*', visibleStart: 0, visibleEnd: 4)]
 * public string $creditCard; // "4532123456789012" -> "************9012"
 *
 * // Example: Mask email, showing first 2 and last 4 characters
 * #[WithTransformer(MaskTransformer::class, visibleStart: 2, visibleEnd: 4)]
 * public string $email; // "user@example.com" -> "us**********e.com"
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class MaskTransformer implements Transformer
{
    /**
     * Creates a new mask transformer with configurable masking behavior.
     *
     * @param string $mask         The character used to replace hidden portions of the string.
     *                             Defaults to asterisk (*) but can be any single character or
     *                             multi-character string (repeated for each hidden character).
     * @param int    $visibleStart Number of characters to keep visible at the start of the string.
     *                             Defaults to 0, hiding all starting characters. Useful for
     *                             showing account prefixes or identifying initials.
     * @param int    $visibleEnd   Number of characters to keep visible at the end of the string.
     *                             Defaults to 4, which works well for credit card last-four digits
     *                             or account number suffixes used for identification.
     */
    public function __construct(
        private string $mask = '*',
        private int $visibleStart = 0,
        private int $visibleEnd = 4,
    ) {}

    /**
     * Applies masking to string values while preserving configured visible portions.
     *
     * Replaces the middle section of strings with the mask character, keeping the specified
     * number of characters visible at the start and end. If the string is too short to
     * safely reveal portions, masks the entire string to prevent information disclosure.
     *
     * @param  DataProperty          $property Metadata about the property being transformed
     * @param  mixed                 $value    The value to mask (strings are masked, other types unchanged)
     * @param  TransformationContext $context  Context information about the transformation operation
     * @return mixed                 Masked string with visible start/end portions, or original value if not a string
     */
    #[Override()]
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if (mb_strlen($value) <= $this->visibleStart + $this->visibleEnd) {
            return str_repeat($this->mask, mb_strlen($value));
        }

        $start = mb_substr($value, 0, $this->visibleStart);
        $end = $this->visibleEnd > 0 ? mb_substr($value, -$this->visibleEnd) : '';
        $middle = str_repeat($this->mask, mb_strlen($value) - $this->visibleStart - $this->visibleEnd);

        return $start.$middle.$end;
    }
}
