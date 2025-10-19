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
 * Validates that a numeric field is positive (greater than zero).
 *
 * This attribute ensures the field value is greater than zero. Implemented
 * using Laravel's 'gt' (greater than) validation rule with 0 as the comparison
 * value. Useful for validating quantities, prices, or other values that must
 * be strictly positive (excludes zero).
 *
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Positive extends StringValidationAttribute
{
    /**
     * Returns the Laravel validation rule keyword.
     *
     * Uses 'gt' (greater than) rule to enforce positive values.
     *
     * @return string The validation rule keyword 'gt'
     */
    #[Override()]
    public static function keyword(): string
    {
        return 'gt';
    }

    /**
     * Returns the parameters for the validation rule.
     *
     * Provides 0 as the comparison value, ensuring the field must be greater than zero.
     *
     * @return array<int, mixed> Array containing 0 as the comparison threshold
     */
    #[Override()]
    public function parameters(): array
    {
        return [0];
    }
}
