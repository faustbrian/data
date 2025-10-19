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
 * Validates that a numeric field is negative (less than zero).
 *
 * This attribute ensures the field value is less than zero. Implemented
 * using Laravel's 'lt' (less than) validation rule with 0 as the comparison
 * value. Useful for validating debits, losses, or other values that must
 * be negative.
 *
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Negative extends StringValidationAttribute
{
    /**
     * Returns the Laravel validation rule keyword.
     *
     * Uses 'lt' (less than) rule to enforce negative values.
     *
     * @return string The validation rule keyword 'lt'
     */
    #[Override()]
    public static function keyword(): string
    {
        return 'lt';
    }

    /**
     * Returns the parameters for the validation rule.
     *
     * Provides 0 as the comparison value, ensuring the field must be less than zero.
     *
     * @return array<int, mixed> Array containing 0 as the comparison threshold
     */
    #[Override()]
    public function parameters(): array
    {
        return [0];
    }
}
