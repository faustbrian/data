<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\SubstrCast;
use Spatie\LaravelData\Data;

describe('SubstrCast', function (): void {
    describe('Happy Paths', function (): void {
        test('extracts substring from start', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 6)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe('World');
        });

        test('extracts substring with start and length', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 0, length: 5)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe('Hello');
        });

        test('extracts substring with negative start', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: -5)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe('World');
        });

        test('extracts substring with negative length', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 0, length: -6)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe('Hello');
        });

        test('extracts entire string with start 0 and null length', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 0)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe('Hello World');
        });

        test('extracts empty string when start equals length', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 5)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello']);

            expect($data->field)->toBe('');
        });
    });

    describe('Edge Cases', function (): void {
        test('returns non-string values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 0, length: 5)]
                    public readonly int $field = 0,
                ) {}
            };

            $data = $dataClass::from(['field' => 123]);

            expect($data->field)->toBe(123);
        });

        test('returns null unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 0, length: 5)]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => null]);

            expect($data->field)->toBeNull();
        });

        test('returns array unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 0, length: 5)]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['test']]);

            expect($data->field)->toBe(['test']);
        });

        test('handles multibyte characters correctly', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 0, length: 2)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Ñoño']);

            expect($data->field)->toBe('Ño');
        });

        test('handles unicode characters correctly', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 1, length: 2)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '日本語']);

            expect($data->field)->toBe('本語');
        });

        test('handles empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 0, length: 5)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '']);

            expect($data->field)->toBe('');
        });

        test('handles start beyond string length', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 100)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello']);

            expect($data->field)->toBe('');
        });

        test('handles length larger than remaining string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 2, length: 100)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello']);

            expect($data->field)->toBe('llo');
        });

        test('handles negative start beyond string length', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: -100)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello']);

            expect($data->field)->toBe('Hello');
        });

        test('extracts middle of string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 4, length: 5)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'The quick brown']);

            expect($data->field)->toBe('quick');
        });

        test('handles single character extraction', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SubstrCast(start: 1, length: 1)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello']);

            expect($data->field)->toBe('e');
        });
    });
});
