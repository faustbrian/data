<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\NumberLikeCast;
use Spatie\LaravelData\Data;

describe('NumberLikeCast', function (): void {
    describe('Happy Paths', function (): void {
        test('casts integer to string by default', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => 42]);

            expect($data->amount)->toBe('42');
        });

        test('casts float to string by default', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => 42.5]);

            expect($data->amount)->toBe('42.5');
        });

        test('casts numeric string to normalized string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '123.45']);

            expect($data->amount)->toBe('123.45');
        });

        test('casts string with comma decimal to normalized string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '123,45']);

            expect($data->amount)->toBe('123.45');
        });

        test('casts string with thousand separator to normalized string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '1,234.56']);

            expect($data->amount)->toBe('1234.56');
        });

        test('casts to integer when as parameter is int', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast(as: 'int')]
                    public readonly int $amount = 0,
                ) {}
            };

            $data = $dataClass::from(['amount' => '42']);

            expect($data->amount)->toBe(42);
        });

        test('casts to float when as parameter is float', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast(as: 'float')]
                    public readonly float $amount = 0.0,
                ) {}
            };

            $data = $dataClass::from(['amount' => '42.7']);

            expect($data->amount)->toBe(42.7);
        });

        test('handles negative numbers', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => -42.5]);

            expect($data->amount)->toBe('-42.5');
        });

        test('handles positive sign prefix', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '+42.5']);

            expect($data->amount)->toBe('+42.5');
        });

        test('handles zero', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => 0]);

            expect($data->amount)->toBe('0');
        });

        test('handles zero as string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '0']);

            expect($data->amount)->toBe('0');
        });
    });

    describe('Sad Paths', function (): void {
        test('throws exception for invalid as parameter', function (): void {
            expect(function (): void {
                new NumberLikeCast(as: 'invalid');
            })->toThrow(InvalidArgumentException::class);
        });

        test('returns null for non-numeric string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly ?string $amount = null,
                ) {}
            };

            $data = $dataClass::from(['amount' => 'not a number']);

            expect($data->amount)->toBeNull();
        });

        test('returns null for array input', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly ?string $amount = null,
                ) {}
            };

            $data = $dataClass::from(['amount' => []]);

            expect($data->amount)->toBeNull();
        });

        test('returns null for object input', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly ?string $amount = null,
                ) {}
            };

            $data = $dataClass::from(['amount' => new stdClass()]);

            expect($data->amount)->toBeNull();
        });

        test('returns null for null input', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly ?string $amount = null,
                ) {}
            };

            $data = $dataClass::from(['amount' => null]);

            expect($data->amount)->toBeNull();
        });

        test('returns null for boolean input', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly ?string $amount = null,
                ) {}
            };

            $data = $dataClass::from(['amount' => true]);

            expect($data->amount)->toBeNull();
        });
    });

    describe('Edge Cases', function (): void {
        test('handles string with spaces', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '  42.5  ']);

            expect($data->amount)->toBe('42.5');
        });

        test('handles string with NBSP thousand separator', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '1 234.56']);

            expect($data->amount)->toBe('1234.56');
        });

        test('handles empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly ?string $amount = null,
                ) {}
            };

            $data = $dataClass::from(['amount' => '']);

            expect($data->amount)->toBeNull();
        });

        test('handles very large numbers', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '999999999999999']);

            expect($data->amount)->toBe('999999999999999');
        });

        test('handles very small decimal numbers', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '0.0001']);

            expect($data->amount)->toBe('0.0001');
        });

        test('handles number with many decimal places', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '3.14159265359']);

            expect($data->amount)->toBe('3.14159265359');
        });

        test('truncates decimal when casting to int', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast(as: 'int')]
                    public readonly int $amount = 0,
                ) {}
            };

            $data = $dataClass::from(['amount' => '42.9']);

            expect($data->amount)->toBe(42);
        });

        test('handles negative zero', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '-0']);

            expect($data->amount)->toBe('0');
        });

        test('handles string with only whitespace', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly ?string $amount = null,
                ) {}
            };

            $data = $dataClass::from(['amount' => '   ']);

            expect($data->amount)->toBeNull();
        });

        test('handles mixed comma and dot separators with comma as thousands', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast()]
                    public readonly string $amount = '',
                ) {}
            };

            $data = $dataClass::from(['amount' => '12,345.67']);

            expect($data->amount)->toBe('12345.67');
        });

        test('preserves precision for float type', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[NumberLikeCast(as: 'float')]
                    public readonly float $amount = 0.0,
                ) {}
            };

            $data = $dataClass::from(['amount' => '123.456789']);

            expect($data->amount)->toBeFloat();
        });
    });
});
