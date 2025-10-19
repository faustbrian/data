<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\PresentWithAll;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\RequiringRule;

describe('PresentWithAll', function (): void {
    describe('Happy Paths', function (): void {
        test('validates when field is present and all other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    #[PresentWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com', 'phone' => '123-456-7890', 'username' => 'john_doe']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is not present and not all other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[PresentWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when all fields including target are missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[PresentWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $validator = Validator::make([], $dataClass::getValidationRules([]));

            expect($validator->passes())->toBeTrue();
        });

        test('validates when field is present with null and all other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    #[PresentWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com', 'phone' => '123-456-7890', 'username' => null]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present with empty string and all other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    #[PresentWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com', 'phone' => '123-456-7890', 'username' => '']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects when field is not present and all other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    #[PresentWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com', 'phone' => '123-456-7890']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when all other fields are present with empty strings and field missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    #[PresentWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => '', 'phone' => '']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when all other fields are present with null and field missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    #[PresentWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => null, 'phone' => null]))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when all three required fields are present and target missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    public readonly ?string $address = null,
                    #[PresentWithAll('email', 'phone', 'address')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com', 'phone' => '123', 'address' => '123 Main']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('rejects when all fields present with zero and target missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    public readonly ?int $total = null,
                    #[PresentWithAll('count', 'total')]
                    public readonly ?string $result = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['count' => 0, 'total' => 0]))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');
    });

    describe('Edge Cases', function (): void {
        test('validates with array of other fields when not all are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[PresentWithAll(['email', 'phone', 'address'])]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with single field when it is not present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[PresentWithAll('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $validator = Validator::make([], $dataClass::getValidationRules([]));

            expect($validator->passes())->toBeTrue();
        });

        test('rejects with single field when it is present and target missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[PresentWithAll('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com']))
                ->toThrow(ValidationException::class);
        })->skip('present_* validation rules are incompatible with Spatie Data - use required_* rules instead');

        test('validates with nested field names when not all present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[PresentWithAll('profile.email', 'profile.phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['profile.email' => 'test@example.com']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with nested field names when all are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[PresentWithAll('profile.email', 'profile.phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['profile.email' => 'test@example.com', 'profile.phone' => '123', 'username' => 'john']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field has zero and all other fields present with values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    public readonly ?int $total = null,
                    #[PresentWithAll('count', 'total')]
                    public readonly ?int $result = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => 5, 'total' => 10, 'result' => 0]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field has false and all other fields present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    public readonly bool $active = false,
                    #[PresentWithAll('enabled', 'active')]
                    public readonly bool $visible = false,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['enabled' => true, 'active' => true, 'visible' => false]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new PresentWithAll('field');

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('implements RequiringRule', function (): void {
            $attribute = new PresentWithAll('field');

            expect($attribute)->toBeInstanceOf(RequiringRule::class);
        });

        test('returns correct keyword', function (): void {
            expect(PresentWithAll::keyword())->toBe('present_with_all');
        });

        test('returns parameters with single field', function (): void {
            $attribute = new PresentWithAll('email');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(1);
        });

        test('returns parameters with multiple fields', function (): void {
            $attribute = new PresentWithAll('email', 'phone', 'address');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(3);
        });

        test('returns parameters with array of fields', function (): void {
            $attribute = new PresentWithAll(['email', 'phone']);
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(2);
        });
    });
});
