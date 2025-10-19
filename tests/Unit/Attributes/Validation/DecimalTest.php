<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\Decimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;

describe('Decimal', function (): void {
    describe('Happy Paths', function (): void {
        test('validates decimal with minimum decimal places', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2)]
                    public readonly ?string $price = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['price' => '10.50']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates decimal with exact minimum decimal places', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2)]
                    public readonly ?string $amount = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['amount' => '100.00']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates decimal with min and max decimal places', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2, max: 4)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['value' => '99.999']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates zero with decimal places', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2)]
                    public readonly ?string $amount = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['amount' => '0.00']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates negative decimal with decimal places', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2)]
                    public readonly ?string $temperature = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['temperature' => '-15.75']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates decimal with more than minimum places', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2, max: 5)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['value' => '10.12345']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects integer without decimal places', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2)]
                    public readonly ?string $price = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['price' => '10']))
                ->toThrow(ValidationException::class);
        });

        test('rejects decimal with fewer than minimum places', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2)]
                    public readonly ?string $amount = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['amount' => '10.5']))
                ->toThrow(ValidationException::class);
        });

        test('rejects decimal with more than maximum places', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2, max: 3)]
                    public readonly ?string $value = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['value' => '10.12345']))
                ->toThrow(ValidationException::class);
        });

        test('rejects non-numeric string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2)]
                    public readonly ?string $price = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['price' => 'abc']))
                ->toThrow(ValidationException::class);
        });

        test('rejects decimal with zero places when minimum required', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2)]
                    public readonly ?string $amount = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['amount' => '100.']))
                ->toThrow(ValidationException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('validates very large decimal number', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['value' => '999999999.99']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates very small decimal number', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 4)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['value' => '0.0001']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates decimal with maximum decimal places at boundary', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2, max: 2)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['value' => '10.12']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates decimal with single digit minimum', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 1)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['value' => '10.5']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('handles scientific notation', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2)]
                    public readonly ?string $value = null,
                ) {}
            };

            $validator = Validator::make(['value' => '1.23e2'], $dataClass::getValidationRules(['value' => '1.23e2']));

            expect($validator->fails())->toBeTrue();
        });

        test('validates decimal with leading zeros', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Decimal(min: 2)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['value' => '00.50']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new Decimal(min: 2);

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('returns correct keyword', function (): void {
            expect(Decimal::keyword())->toBe('decimal');
        });

        test('returns parameters with only min when max is null', function (): void {
            $attribute = new Decimal(min: 2);

            expect($attribute->parameters())->toBe([2]);
        });

        test('returns parameters with min and max', function (): void {
            $attribute = new Decimal(min: 2, max: 4);

            expect($attribute->parameters())->toBe([2, 4]);
        });

        test('accepts float for min parameter', function (): void {
            $attribute = new Decimal(min: 2.5);

            expect($attribute->parameters())->toBe([2.5]);
        });

        test('accepts int for min parameter', function (): void {
            $attribute = new Decimal(min: 2);

            expect($attribute->parameters())->toBe([2]);
        });
    });
});
