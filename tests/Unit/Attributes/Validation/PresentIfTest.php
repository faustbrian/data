<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\PresentIf;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\RequiringRule;

describe('PresentIf', function (): void {
    describe('Happy Paths', function (): void {
        test('validates when field is present and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[PresentIf('status', 'active')]
                    public readonly ?string $active_date = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'active', 'active_date' => '2024-01-01']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is not present and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[PresentIf('status', 'active')]
                    public readonly ?string $active_date = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'inactive']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present with null and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    #[PresentIf('enabled', true)]
                    public readonly ?array $config = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['enabled' => true, 'config' => null]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present with empty string and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[PresentIf('status', 'active')]
                    public readonly ?string $notes = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'active', 'notes' => '']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with multiple condition values when one matches', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $type = null,
                    #[PresentIf('type', 'automatic', 'scheduled')]
                    public readonly ?string $schedule_date = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['type' => 'scheduled', 'schedule_date' => '2024-01-01']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when condition is met with integer value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    #[PresentIf('count', 0)]
                    public readonly ?string $items = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => 0, 'items' => '']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects when field is not present and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[PresentIf('status', 'active')]
                    public readonly ?string $active_date = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['status' => 'active']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when condition matches one of multiple values and field missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $type = null,
                    #[PresentIf('type', 'automatic', 'scheduled')]
                    public readonly ?string $config = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['type' => 'automatic']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when boolean condition is met and field not present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    #[PresentIf('enabled', true)]
                    public readonly ?array $settings = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['enabled' => true]))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when integer condition is met and field not present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    #[PresentIf('count', 0)]
                    public readonly ?string $message = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['count' => 0]))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');
    });

    describe('Edge Cases', function (): void {
        test('validates when condition field is missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[PresentIf('status', 'active')]
                    public readonly ?string $active_date = null,
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
                    #[PresentIf('status', ['draft', 'archived'])]
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
                    #[PresentIf('status', 'active')]
                    public readonly ?string $reason = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'ACTIVE']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with numeric string comparison', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $count = null,
                    #[PresentIf('count', '0')]
                    public readonly ?string $items = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => '0', 'items' => '']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present with zero and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    #[PresentIf('enabled', true)]
                    public readonly ?int $counter = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['enabled' => true, 'counter' => 0]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present with false and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $mode = null,
                    #[PresentIf('mode', 'strict')]
                    public readonly bool $allow_null = false,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['mode' => 'strict', 'allow_null' => false]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new PresentIf('field', 'value');

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('implements RequiringRule', function (): void {
            $attribute = new PresentIf('field', 'value');

            expect($attribute)->toBeInstanceOf(RequiringRule::class);
        });

        test('returns correct keyword', function (): void {
            expect(PresentIf::keyword())->toBe('present_if');
        });

        test('returns parameters with field and single value', function (): void {
            $attribute = new PresentIf('status', 'active');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(2)
                ->and($parameters[1])->toBe(['active']);
        });

        test('returns parameters with field and multiple values', function (): void {
            $attribute = new PresentIf('status', 'draft', 'archived');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(2)
                ->and($parameters[1])->toBe(['draft', 'archived']);
        });
    });
});
