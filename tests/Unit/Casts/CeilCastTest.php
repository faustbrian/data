<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\CeilCast;
use Spatie\LaravelData\Data;

describe('CeilCast', function (): void {
    describe('Happy Paths', function (): void {
        test('casts float to ceiling', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CeilCast()]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 4.3]);

            expect($data->field)->toBe(5.0);
        });

        test('casts negative float to ceiling', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CeilCast()]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => -4.3]);

            expect($data->field)->toBe(-4.0);
        });

        test('handles integer values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CeilCast()]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 5]);

            expect($data->field)->toBe(5.0);
        });

        test('handles numeric strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CeilCast()]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => '4.7']);

            expect($data->field)->toBe(5.0);
        });

        test('handles zero', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CeilCast()]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 0]);

            expect($data->field)->toBe(0.0);
        });
    });

    describe('Edge Cases', function (): void {
        test('returns non-numeric values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CeilCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'not a number']);

            expect($data->field)->toBe('not a number');
        });

        test('returns null unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CeilCast()]
                    public readonly ?float $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => null]);

            expect($data->field)->toBeNull();
        });

        test('returns array unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CeilCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => [1, 2, 3]]);

            expect($data->field)->toBe([1, 2, 3]);
        });

        test('handles very small decimal values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CeilCast()]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 0.000_1]);

            expect($data->field)->toBe(1.0);
        });

        test('handles large values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CeilCast()]
                    public readonly float $field = 0.0,
                ) {}
            };

            $data = $dataClass::from(['field' => 999_999.1]);

            expect($data->field)->toBe(1_000_000.0);
        });
    });
});
