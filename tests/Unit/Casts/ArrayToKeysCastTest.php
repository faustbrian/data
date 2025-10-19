<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\ArrayToKeysCast;
use Spatie\LaravelData\Data;

describe('ArrayToKeysCast', function (): void {
    describe('Happy Paths', function (): void {
        test('extracts keys from associative array', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToKeysCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['name' => 'John', 'age' => 30, 'city' => 'NYC']]);

            expect($data->field)->toBe(['name', 'age', 'city']);
        });

        test('extracts numeric keys from indexed array', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToKeysCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['apple', 'banana', 'cherry']]);

            expect($data->field)->toBe([0, 1, 2]);
        });

        test('handles empty array', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToKeysCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => []]);

            expect($data->field)->toBe([]);
        });

        test('extracts keys from mixed array', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToKeysCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['a' => 1, 'b' => 2, 3]]);

            expect($data->field)->toBe(['a', 'b', 0]);
        });
    });

    describe('Edge Cases', function (): void {
        test('returns non-array values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToKeysCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'not an array']);

            expect($data->field)->toBe('not an array');
        });

        test('returns null unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToKeysCast()]
                    public readonly ?array $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => null]);

            expect($data->field)->toBeNull();
        });

        test('returns integer unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToKeysCast()]
                    public readonly int $field = 0,
                ) {}
            };

            $data = $dataClass::from(['field' => 123]);

            expect($data->field)->toBe(123);
        });

        test('handles array with nested arrays', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToKeysCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['users' => ['John', 'Jane'], 'admins' => ['Admin']]]);

            expect($data->field)->toBe(['users', 'admins']);
        });

        test('preserves key order', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToKeysCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['z' => 1, 'a' => 2, 'm' => 3]]);

            expect($data->field)->toBe(['z', 'a', 'm']);
        });

        test('handles array with single element', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToKeysCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['only' => 'one']]);

            expect($data->field)->toBe(['only']);
        });
    });
});
