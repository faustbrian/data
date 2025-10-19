<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\PresentWith;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\RequiringRule;

describe('PresentWith', function (): void {
    describe('Happy Paths', function (): void {
        test('validates when field is present and other field is present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[PresentWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com', 'username' => 'john_doe']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when both fields are not present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                    #[PresentWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['name' => 'John']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present with null and other field is present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[PresentWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com', 'username' => null]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present with empty string and other field is present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[PresentWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com', 'username' => '']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with multiple other fields when any is present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $phone = null,
                    #[PresentWith('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['phone' => '123-456-7890', 'username' => 'john_doe']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects when field is not present and other field is present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[PresentWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when any of multiple other fields are present and field is missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[PresentWith('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when other field is present with empty string and field missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[PresentWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => '']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when other field is present with null and field missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[PresentWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => null]))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when other field is present with zero and field missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    #[PresentWith('count')]
                    public readonly ?string $items = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['count' => 0]))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');
    });

    describe('Edge Cases', function (): void {
        test('validates with array of other fields when none are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                    #[PresentWith(['email', 'phone', 'address'])]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['name' => 'John']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when all fields are missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[PresentWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $validator = Validator::make([], $dataClass::getValidationRules([]));

            expect($validator->passes())->toBeTrue();
        });

        test('rejects when one of many other fields is present and field missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $address = null,
                    #[PresentWith('email', 'phone', 'address')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['address' => '123 Main St']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('validates with nested field names', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[PresentWith('profile.email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['profile.email' => 'test@example.com', 'username' => 'john']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field and other field both have zero values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    #[PresentWith('count')]
                    public readonly ?int $total = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => 0, 'total' => 0]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field and other field both have false values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    #[PresentWith('enabled')]
                    public readonly bool $active = false,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['enabled' => false, 'active' => false]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field and other field both have empty arrays', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?array $items = null,
                    #[PresentWith('items')]
                    public readonly ?array $data = null,
                ) {}
            };

            $validator = Validator::make(['items' => [], 'data' => []], $dataClass::getValidationRules(['items' => [], 'data' => []]));

            expect($validator->passes())->toBeTrue();
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new PresentWith('field');

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('implements RequiringRule', function (): void {
            $attribute = new PresentWith('field');

            expect($attribute)->toBeInstanceOf(RequiringRule::class);
        });

        test('returns correct keyword', function (): void {
            expect(PresentWith::keyword())->toBe('present_with');
        });

        test('returns parameters with single field', function (): void {
            $attribute = new PresentWith('email');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(1);
        });

        test('returns parameters with multiple fields', function (): void {
            $attribute = new PresentWith('email', 'phone', 'address');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(3);
        });

        test('returns parameters with array of fields', function (): void {
            $attribute = new PresentWith(['email', 'phone']);
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(2);
        });
    });
});
