<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Attributes\Validation;

use Attribute;
use BackedEnum;
use Illuminate\Support\Arr;
use Override;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Support\Validation\References\FieldReference;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\RequiringRule;

/**
 * Validates that a field is missing when another field equals a specific value.
 *
 * This attribute ensures the field key does not exist in the input
 * when another field matches one of the specified values. Useful for
 * conditional validation where certain fields should be absent based
 * on the state of other fields.
 *
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class MissingIf extends StringValidationAttribute implements RequiringRule
{
    /**
     * The field to check for comparison.
     */
    private readonly FieldReference $field;

    /**
     * The values to compare against the specified field.
     *
     * @var array<int|string, mixed>
     */
    private readonly array $values;

    /**
     * Create a new missing_if validation attribute instance.
     *
     * @param FieldReference|string                                                       $field     The field name to check against. Can be a string
     *                                                                                               field name or a FieldReference for nested field paths
     *                                                                                               using dot notation (e.g., 'user.role').
     * @param array<int|string, mixed>|BackedEnum|bool|int|RouteParameterReference|string ...$values The values to compare against the field.
     *                                                                                               When the specified field matches any of these
     *                                                                                               values, this field must be missing. Supports
     *                                                                                               primitive types, enums, arrays, and route
     *                                                                                               parameter references for dynamic validation.
     */
    public function __construct(
        string|FieldReference $field,
        array|string|int|bool|BackedEnum|RouteParameterReference ...$values,
    ) {
        $this->field = $this->parseFieldReference($field);
        $this->values = Arr::flatten($values);
    }

    /**
     * Returns the Laravel validation rule keyword.
     *
     * @return string The validation rule keyword 'missing_if'
     */
    #[Override()]
    public static function keyword(): string
    {
        return 'missing_if';
    }

    /**
     * Returns the parameters for the validation rule.
     *
     * @return array<int, mixed> Array containing the field reference and comparison values
     */
    #[Override()]
    public function parameters(): array
    {
        return [
            $this->field,
            $this->values,
        ];
    }
}
