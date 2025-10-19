<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\MissingWithAll;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\RequiringRule;

describe('MissingWithAll', function (): void {
    describe('Happy Paths', function (): void {
        test('validates when field is missing and all other fields are not present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                    #[MissingWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['name' => 'John']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present and not all other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[MissingWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com', 'username' => 'john_doe']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when all fields including target are missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[MissingWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate([]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present and only some other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[MissingWithAll('email', 'phone', 'address')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com', 'username' => 'john_doe']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is missing and only some other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[MissingWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects when field is present and all other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    #[MissingWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com', 'phone' => '123-456-7890', 'username' => 'john_doe']))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with value and all other fields are present with empty strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    #[MissingWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => '', 'phone' => '', 'username' => 'john_doe']))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with null and all other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    #[MissingWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com', 'phone' => '123-456-7890', 'username' => null]))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with empty string and all other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    #[MissingWithAll('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com', 'phone' => '123-456-7890', 'username' => '']))
                ->toThrow(ValidationException::class);
        });

        test('rejects with three required fields all present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    public readonly ?string $address = null,
                    #[MissingWithAll('email', 'phone', 'address')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com', 'phone' => '123', 'address' => '123 Main', 'username' => 'john']))
                ->toThrow(ValidationException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('validates with array of other fields when not all are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[MissingWithAll(['email', 'phone', 'address'])]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com', 'username' => 'john_doe']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with single field when it is not present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[MissingWithAll('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['username' => 'john_doe']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('rejects with single field when it is present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[MissingWithAll('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com', 'username' => 'john_doe']))
                ->toThrow(ValidationException::class);
        });

        test('validates with nested field names when not all present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[MissingWithAll('profile.email', 'profile.phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['profile.email' => 'test@example.com']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field has zero and all other fields present with values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    public readonly ?int $total = null,
                    #[MissingWithAll('count', 'total')]
                    public readonly ?int $result = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['count' => 5, 'total' => 10, 'result' => 0]))
                ->toThrow(ValidationException::class);
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new MissingWithAll('field');

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('implements RequiringRule', function (): void {
            $attribute = new MissingWithAll('field');

            expect($attribute)->toBeInstanceOf(RequiringRule::class);
        });

        test('returns correct keyword', function (): void {
            expect(MissingWithAll::keyword())->toBe('missing_with_all');
        });

        test('returns parameters with single field', function (): void {
            $attribute = new MissingWithAll('email');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(1);
        });

        test('returns parameters with multiple fields', function (): void {
            $attribute = new MissingWithAll('email', 'phone', 'address');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(3);
        });

        test('returns parameters with array of fields', function (): void {
            $attribute = new MissingWithAll(['email', 'phone']);
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(2);
        });
    });
});
