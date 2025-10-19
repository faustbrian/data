<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\MissingWith;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\RequiringRule;

describe('MissingWith', function (): void {
    describe('Happy Paths', function (): void {
        test('validates when field is missing and other field is not present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                    #[MissingWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['name' => 'John']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when both fields are missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $id = null,
                    #[MissingWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['id' => 1]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present and other field is not present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[MissingWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['username' => 'john_doe']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with multiple other fields when none are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                    #[MissingWith('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['name' => 'John']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects when field is present and any other field is present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[MissingWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com', 'username' => 'john_doe']))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with value and other field is present with empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[MissingWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => '', 'username' => 'john_doe']))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with null and other field is present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[MissingWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com', 'username' => null]))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present and any of multiple other fields are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $phone = null,
                    #[MissingWith('email', 'phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['phone' => '123-456-7890', 'username' => 'john_doe']))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with empty string and other field is present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[MissingWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => 'test@example.com', 'username' => '']))
                ->toThrow(ValidationException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('validates with array of other fields when none are present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                    #[MissingWith(['email', 'phone', 'address'])]
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
                    #[MissingWith('email')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate([]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('rejects when one of many other fields is present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $address = null,
                    #[MissingWith('email', 'phone', 'address')]
                    public readonly ?string $username = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['address' => '123 Main St', 'username' => 'john_doe']))
                ->toThrow(ValidationException::class);
        });

        test('validates with nested field names', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[MissingWith('profile.phone')]
                    public readonly ?string $username = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['profile.email' => 'test@example.com']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field and other field both have zero values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    #[MissingWith('status')]
                    public readonly ?int $total = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => 0, 'total' => 0]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new MissingWith('field');

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('implements RequiringRule', function (): void {
            $attribute = new MissingWith('field');

            expect($attribute)->toBeInstanceOf(RequiringRule::class);
        });

        test('returns correct keyword', function (): void {
            expect(MissingWith::keyword())->toBe('missing_with');
        });

        test('returns parameters with single field', function (): void {
            $attribute = new MissingWith('email');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(1);
        });

        test('returns parameters with multiple fields', function (): void {
            $attribute = new MissingWith('email', 'phone', 'address');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(3);
        });

        test('returns parameters with array of fields', function (): void {
            $attribute = new MissingWith(['email', 'phone']);
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0])->toHaveCount(2);
        });
    });
});
