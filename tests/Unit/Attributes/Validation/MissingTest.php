<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\Missing;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;

describe('Missing', function (): void {
    describe('Happy Paths', function (): void {
        test('validates when field is not present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                    #[Missing()]
                    public readonly ?string $optional = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['name' => 'John']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is completely absent from input', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $email = null,
                    #[Missing()]
                    public readonly ?string $phone = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['email' => 'test@example.com']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with multiple fields where missing field is absent', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                    public readonly ?string $email = null,
                    #[Missing()]
                    public readonly ?string $metadata = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['name' => 'John', 'email' => 'john@example.com']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects when field is present with value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Missing()]
                    public readonly ?string $name = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['name' => 'John']))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Missing()]
                    public readonly ?string $email = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['email' => '']))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with null value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Missing()]
                    public readonly ?string $value = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['value' => null]))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with zero', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Missing()]
                    public readonly ?int $count = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['count' => 0]))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with false', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Missing()]
                    public readonly bool $active = false,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['active' => false]))
                ->toThrow(ValidationException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('validates with empty input array', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Missing()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate([]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('rejects when field is present in nested array', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Missing()]
                    public readonly ?array $data = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['data' => ['field' => 'value']]))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with empty array', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Missing()]
                    public readonly ?array $items = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['items' => []]))
                ->toThrow(ValidationException::class);
        });

        test('validates with only other fields present', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $other = null,
                    #[Missing()]
                    public readonly ?string $missing = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['other' => 'value']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new Missing();

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('returns correct keyword', function (): void {
            expect(Missing::keyword())->toBe('missing');
        });

        test('returns empty parameters array', function (): void {
            $attribute = new Missing();

            expect($attribute->parameters())->toBe([]);
        });
    });
});
