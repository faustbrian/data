<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\PresentUnless;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\RequiringRule;

describe('PresentUnless', function (): void {
    describe('Happy Paths', function (): void {
        test('validates when field is present and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[PresentUnless('status', 'active')]
                    public readonly ?string $inactive_reason = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'inactive', 'inactive_reason' => 'test']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is not present and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[PresentUnless('status', 'active')]
                    public readonly ?string $inactive_reason = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'active']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present with null and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    #[PresentUnless('enabled', true)]
                    public readonly ?array $config = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['enabled' => false, 'config' => null]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present with empty string and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[PresentUnless('status', 'active')]
                    public readonly ?string $notes = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'pending', 'notes' => '']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with multiple condition values when none match', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $type = null,
                    #[PresentUnless('type', 'automatic', 'scheduled')]
                    public readonly ?string $manual_config = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['type' => 'manual', 'manual_config' => 'test']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when condition is not met with boolean value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    #[PresentUnless('enabled', true)]
                    public readonly ?string $reason = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['enabled' => false, 'reason' => 'disabled']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects when field is not present and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[PresentUnless('status', 'active')]
                    public readonly ?string $inactive_reason = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['status' => 'inactive']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when condition does not match any of multiple values and field missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $type = null,
                    #[PresentUnless('type', 'automatic', 'scheduled')]
                    public readonly ?string $config = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['type' => 'manual']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when boolean condition is not met and field not present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    #[PresentUnless('enabled', true)]
                    public readonly ?array $settings = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['enabled' => false]))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when integer condition is not met and field not present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    #[PresentUnless('count', 0)]
                    public readonly ?string $items = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['count' => 5]))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');
    });

    describe('Edge Cases', function (): void {
        test('validates when condition field is missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[PresentUnless('status', 'active')]
                    public readonly ?string $inactive_reason = null,
                ) {}
            };

            $validator = Validator::make([], $dataClass::getValidationRules([]));

            expect($validator->passes())->toBeTrue();
        });

        test('handles array of condition values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[PresentUnless('status', ['published', 'archived'])]
                    public readonly ?string $draft_notes = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'draft', 'draft_notes' => 'test']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates case sensitive string comparison', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[PresentUnless('status', 'active')]
                    public readonly ?string $reason = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'ACTIVE', 'reason' => 'test']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with numeric string comparison', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $count = null,
                    #[PresentUnless('count', '0')]
                    public readonly ?string $items = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => '5', 'items' => 'data']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present with zero and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    #[PresentUnless('enabled', true)]
                    public readonly ?int $counter = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['enabled' => false, 'counter' => 0]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present with false and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $mode = null,
                    #[PresentUnless('mode', 'strict')]
                    public readonly bool $allow_null = false,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['mode' => 'relaxed', 'allow_null' => false]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when condition matches but field is present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[PresentUnless('status', 'active')]
                    public readonly ?string $notes = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'active', 'notes' => 'should not be here']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new PresentUnless('field', 'value');

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('implements RequiringRule', function (): void {
            $attribute = new PresentUnless('field', 'value');

            expect($attribute)->toBeInstanceOf(RequiringRule::class);
        });

        test('returns correct keyword', function (): void {
            expect(PresentUnless::keyword())->toBe('present_unless');
        });

        test('returns parameters with field and single value', function (): void {
            $attribute = new PresentUnless('status', 'active');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(2)
                ->and($parameters[1])->toBe(['active']);
        });

        test('returns parameters with field and multiple values', function (): void {
            $attribute = new PresentUnless('status', 'active', 'pending');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(2)
                ->and($parameters[1])->toBe(['active', 'pending']);
        });
    });
});
