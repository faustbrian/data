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
 * Validates that a field is missing from the input data.
 *
 * This attribute ensures the field key does not exist in the input
 * data at all. Useful for validating that certain fields are not
 * present in requests, such as ensuring clients don't send read-only
 * or system-managed fields.
 *
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Missing extends StringValidationAttribute
{
    /**
     * Returns the Laravel validation rule keyword.
     *
     * @return string The validation rule keyword 'missing'
     */
    #[Override()]
    public static function keyword(): string
    {
        return 'missing';
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
