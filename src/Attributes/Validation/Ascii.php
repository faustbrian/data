<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Attributes\Validation;

use Attribute;
use Override;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;

/**
 * Validates that a field contains only ASCII characters.
 *
 * This attribute ensures the field value contains only characters
 * in the ASCII character set (0-127). Useful for validating
 * usernames, slugs, or other fields that must not contain
 * extended Unicode characters.
 *
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Ascii extends StringValidationAttribute
{
    /**
     * Returns the Laravel validation rule keyword.
     *
     * @return string The validation rule keyword 'ascii'
     */
    #[Override()]
    public static function keyword(): string
    {
        return 'ascii';
    }

    /**
     * Returns the parameters for the validation rule.
     *
     * @return array<int, mixed> Empty array as this rule requires no parameters
     */
    #[Override()]
    public function parameters(): array
    {
        return [];
    }
}
