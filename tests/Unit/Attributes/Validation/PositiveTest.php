<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\Positive;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;

describe('Positive', function (): void {
    describe('Happy Paths', function (): void {
        test('validates positive integer', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?int $count = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => 10]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates positive float', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?float $amount = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['amount' => 25.50]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates positive decimal string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?string $price = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['price' => '100.25']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates very small positive number', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?float $value = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['value' => 0.001]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates large positive number', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?int $total = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['total' => 999_999]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects zero', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?int $value = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['value' => 0]))
                ->toThrow(ValidationException::class);
        });

        test('rejects negative integer', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?int $count = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['count' => -10]))
                ->toThrow(ValidationException::class);
        });

        test('rejects negative float', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?float $amount = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['amount' => -25.50]))
                ->toThrow(ValidationException::class);
        });

        test('rejects negative decimal string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?string $price = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['price' => '-100.25']))
                ->toThrow(ValidationException::class);
        });

        test('rejects non-numeric string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?string $value = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['value' => 'abc']))
                ->toThrow(ValidationException::class);
        });

        test('rejects zero float', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?float $amount = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['amount' => 0.0]))
                ->toThrow(ValidationException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('validates positive one', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?int $value = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['value' => 1]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('rejects boundary value of zero', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?int $value = null,
                ) {}
            };

            $validator = Validator::make(['value' => 0], $dataClass::getValidationRules(['value' => 0]));

            expect($validator->fails())->toBeTrue();
        });

        test('validates positive numeric string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?string $count = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => '5']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('rejects zero string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?string $value = null,
                ) {}
            };

            $validator = Validator::make(['value' => '0'], $dataClass::getValidationRules(['value' => '0']));

            expect($validator->fails())->toBeTrue();
        });

        test('validates very large positive number', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?int $value = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['value' => \PHP_INT_MAX]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('rejects negative boundary value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Positive()]
                    public readonly ?int $value = null,
                ) {}
            };

            $validator = Validator::make(['value' => -1], $dataClass::getValidationRules(['value' => -1]));

            expect($validator->fails())->toBeTrue();
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new Positive();

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('returns correct keyword', function (): void {
            expect(Positive::keyword())->toBe('gt');
        });

        test('returns parameters with zero', function (): void {
            $attribute = new Positive();

            expect($attribute->parameters())->toBe([0]);
        });
    });
});
