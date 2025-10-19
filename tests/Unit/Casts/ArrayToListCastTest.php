<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\ArrayToListCast;
use Spatie\LaravelData\Data;

describe('ArrayToListCast', function (): void {
    describe('Happy Paths', function (): void {
        test('extracts values from associative array', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToListCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['name' => 'John', 'age' => 30, 'city' => 'NYC']]);

            expect($data->field)->toBe(['John', 30, 'NYC']);
        });

        test('reindexes indexed array', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToListCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['apple', 'banana', 'cherry']]);

            expect($data->field)->toBe(['apple', 'banana', 'cherry']);
        });

        test('handles empty array', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToListCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => []]);

            expect($data->field)->toBe([]);
        });

        test('reindexes array with gaps', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToListCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => [0 => 'a', 2 => 'b', 5 => 'c']]);

            expect($data->field)->toBe(['a', 'b', 'c']);
            expect(array_keys($data->field))->toBe([0, 1, 2]);
        });
    });

    describe('Edge Cases', function (): void {
        test('returns non-array values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToListCast()]
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
                    #[ArrayToListCast()]
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
                    #[ArrayToListCast()]
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
                    #[ArrayToListCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['users' => ['John', 'Jane'], 'admins' => ['Admin']]]);

            expect($data->field)->toBe([['John', 'Jane'], ['Admin']]);
        });

        test('preserves value order', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToListCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['z' => 'zebra', 'a' => 'apple', 'm' => 'mango']]);

            expect($data->field)->toBe(['zebra', 'apple', 'mango']);
        });

        test('handles array with single element', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToListCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['only' => 'one']]);

            expect($data->field)->toBe(['one']);
        });

        test('handles mixed value types', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ArrayToListCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['str' => 'text', 'num' => 42, 'bool' => true, 'null' => null]]);

            expect($data->field)->toBe(['text', 42, true, null]);
        });
    });
});
