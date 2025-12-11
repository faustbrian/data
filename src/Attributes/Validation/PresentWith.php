<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Override;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Support\Validation\References\FieldReference;
use Spatie\LaravelData\Support\Validation\RequiringRule;

use function assert;
use function is_string;

/**
 * Validates that a field is present (key exists in input) when any of the specified fields are present.
 *
 * This attribute ensures the field key exists in the input data when at least
 * one of the specified fields is present. Useful for enforcing that related
 * fields must appear together. Note that "present" only checks for key
 * existence, not value - the field can be null or empty.
 *
 * @author Brian Faust <brian@cline.sh>
 * @deprecated This validation rule is incompatible with Spatie Laravel Data's architecture.
 *             The present_* rules check if array keys exist in the input, but Spatie Data
 *             doesn't include optional properties with defaults in the validation payload
 *             when they're missing from input. Even with RequiringRule, if a key isn't in
 *             the input array, Laravel validation never sees it to check for presence.
 *
 *             Use RequiredWith instead, which checks for non-empty values rather than key existence.
 *             Spatie Data provides RequiredWith out of the box: Spatie\LaravelData\Attributes\Validation\RequiredWith
 * @see \Spatie\LaravelData\Attributes\Validation\RequiredWith Use this instead
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class PresentWith extends StringValidationAttribute implements RequiringRule
{
    /**
     * The field references to check for presence.
     *
     * @var array<int, FieldReference>
     */
    private array $fields = [];

    /**
     * Create a new present_with validation attribute instance.
     *
     * @param array<int|string, mixed>|FieldReference|string ...$fields The fields to check for presence.
     *                                                                  When any of these fields are present
     *                                                                  in the input, this field must also be present.
     *                                                                  Accepts field names as strings, FieldReference
     *                                                                  objects for complex paths, or arrays of either.
     *                                                                  All values are flattened and converted to
     *                                                                  FieldReference objects internally.
     */
    public function __construct(array|string|FieldReference ...$fields)
    {
        foreach (Arr::flatten($fields) as $field) {
            assert(is_string($field) || $field instanceof FieldReference);
            $this->fields[] = $field instanceof FieldReference ? $field : new FieldReference($field);
        }
    }

    /**
     * Returns the Laravel validation rule keyword.
     *
     * @return string The validation rule keyword 'present_with'
     */
    #[Override()]
    public static function keyword(): string
    {
        return 'present_with';
    }

    /**
     * Returns the parameters for the validation rule.
     *
     * @return array<int, mixed> Array containing the field references to check
     */
    #[Override()]
    public function parameters(): array
    {
        return [
            $this->fields,
        ];
    }
}
