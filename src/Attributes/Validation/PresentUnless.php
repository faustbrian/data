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
 * Validates that a field is present (key exists in input) unless another field equals a value.
 *
 * This attribute ensures the field key exists in the input data unless another
 * field matches one of the specified values. Inverse of PresentIf - the field
 * must be present except when the condition is met. Note that "present" only
 * checks for key existence, not value.
 *
 * @deprecated This validation rule is incompatible with Spatie Laravel Data's architecture.
 *             The present_* rules check if array keys exist in the input, but Spatie Data
 *             doesn't include optional properties with defaults in the validation payload
 *             when they're missing from input. Even with RequiringRule, if a key isn't in
 *             the input array, Laravel validation never sees it to check for presence.
 *
 *             Use RequiredUnless instead, which checks for non-empty values rather than key existence.
 *             Spatie Data provides RequiredUnless out of the box: Spatie\LaravelData\Attributes\Validation\RequiredUnless
 * @see \Spatie\LaravelData\Attributes\Validation\RequiredUnless Use this instead
 *
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class PresentUnless extends StringValidationAttribute implements RequiringRule
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
     * Create a new present_unless validation attribute instance.
     *
     * @param FieldReference|string                                                            $field     The field name to check against. Can be a string
     *                                                                                                    field name or a FieldReference for nested field paths
     *                                                                                                    using dot notation (e.g., 'user.type').
     * @param null|array<int|string, mixed>|BackedEnum|bool|int|RouteParameterReference|string ...$values The values to compare against the field.
     *                                                                                                    When the specified field does NOT match any
     *                                                                                                    of these values, this field must be present.
     *                                                                                                    Supports primitive types, enums, arrays, and
     *                                                                                                    route parameter references for dynamic validation.
     */
    public function __construct(
        string|FieldReference $field,
        null|array|string|int|bool|BackedEnum|RouteParameterReference ...$values,
    ) {
        $this->field = $this->parseFieldReference($field);
        $this->values = Arr::flatten($values);
    }

    /**
     * Returns the Laravel validation rule keyword.
     *
     * @return string The validation rule keyword 'present_unless'
     */
    #[Override()]
    public static function keyword(): string
    {
        return 'present_unless';
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
