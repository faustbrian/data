<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\RoundCast;
use Spatie\LaravelData\Data;

describe('RoundCast', function (): void {
    describe('Happy Paths', function (): void {
        test('rounds float to default precision 0', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast()]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 3.6]);

            expect($data->field)->toBe(4.0);
        });

        test('rounds float down to default precision 0', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast()]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 3.4]);

            expect($data->field)->toBe(3.0);
        });

        test('rounds to 2 decimal places', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 2)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 3.141_59]);

            expect($data->field)->toBe(3.14);
        });

        test('rounds integer value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 2)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 5]);

            expect($data->field)->toBe(5.0);
        });

        test('rounds numeric string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 1)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => '3.456']);

            expect($data->field)->toBe(3.5);
        });

        test('rounds negative numbers', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 1)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => -3.456]);

            expect($data->field)->toBe(-3.5);
        });
    });

    describe('Edge Cases', function (): void {
        test('handles PHP_ROUND_HALF_UP mode (default)', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 0, mode: \PHP_ROUND_HALF_UP)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 2.5]);

            expect($data->field)->toBe(3.0);
        });

        test('handles PHP_ROUND_HALF_DOWN mode', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 0, mode: \PHP_ROUND_HALF_DOWN)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 2.5]);

            expect($data->field)->toBe(2.0);
        });

        test('handles PHP_ROUND_HALF_EVEN mode banker rounding down', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 0, mode: \PHP_ROUND_HALF_EVEN)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 2.5]);

            expect($data->field)->toBe(2.0);
        });

        test('handles PHP_ROUND_HALF_EVEN mode banker rounding up', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 0, mode: \PHP_ROUND_HALF_EVEN)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 3.5]);

            expect($data->field)->toBe(4.0);
        });

        test('handles PHP_ROUND_HALF_ODD mode rounds to odd', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 0, mode: \PHP_ROUND_HALF_ODD)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 2.5]);

            expect($data->field)->toBe(3.0);
        });

        test('handles PHP_ROUND_HALF_ODD mode maintains odd', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 0, mode: \PHP_ROUND_HALF_ODD)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 3.4]);

            expect($data->field)->toBe(3.0);
        });

        test('rounds zero', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 2)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 0]);

            expect($data->field)->toBe(0.0);
        });

        test('rounds very small numbers', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 5)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 0.000_000_1]);

            expect($data->field)->toBe(0.0);
        });

        test('rounds very large numbers', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 2)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 9_999_999.999]);

            expect($data->field)->toBe(10_000_000.0);
        });

        test('returns non-numeric string unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'not-a-number']);

            expect($data->field)->toBe('not-a-number');
        });

        test('returns null unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast()]
                    public readonly ?float $field = null,
                ) {}
            };

            $data = $dataClass::from([]);

            expect($data->field)->toBeNull();
        });

        test('returns array unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => [3.14]]);

            expect($data->field)->toBe([3.14]);
        });

        test('handles negative precision', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: -1)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 1_234.5]);

            expect($data->field)->toBe(1_230.0);
        });

        test('rounds float with trailing zeros', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[RoundCast(precision: 3)]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 5.1]);

            expect($data->field)->toBe(5.1);
        });
    });
});
