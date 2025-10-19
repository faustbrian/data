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
 * Validates that a field is missing when any of the specified fields are present.
 *
 * This attribute ensures the field key does not exist in the input
 * when at least one of the specified fields is present. Useful for
 * ensuring mutually exclusive fields or enforcing that certain field
 * combinations are not allowed.
 *
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class MissingWith extends StringValidationAttribute implements RequiringRule
{
    /**
     * The field references to check for presence.
     *
     * @var array<int, FieldReference>
     */
    private array $fields = [];

    /**
     * Create a new missing_with validation attribute instance.
     *
     * @param array<int|string, mixed>|FieldReference|string ...$fields The fields to check for presence.
     *                                                                  When any of these fields are present
     *                                                                  in the input, this field must be missing.
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
     * @return string The validation rule keyword 'missing_with'
     */
    #[Override()]
    public static function keyword(): string
    {
        return 'missing_with';
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
