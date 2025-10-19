<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\ReplaceCast;
use Spatie\LaravelData\Data;

describe('ReplaceCast', function (): void {
    describe('Happy Paths', function (): void {
        test('replaces single occurrence of search string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast('hello', 'goodbye')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'hello world']);

            expect($data->field)->toBe('goodbye world');
        });

        test('replaces multiple occurrences of search string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast('test', 'demo')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'test test test']);

            expect($data->field)->toBe('demo demo demo');
        });

        test('replaces with empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast('remove', '')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'please remove this']);

            expect($data->field)->toBe('please  this');
        });

        test('handles string with no matches', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast('foo', 'bar')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'hello world']);

            expect($data->field)->toBe('hello world');
        });

        test('handles empty input string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast('test', 'demo')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '']);

            expect($data->field)->toBe('');
        });
    });

    describe('Edge Cases', function (): void {
        test('handles unicode characters in search', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast('ñ', 'n')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'niño']);

            expect($data->field)->toBe('nino');
        });

        test('handles unicode characters in replacement', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast('n', 'ñ')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'nino']);

            expect($data->field)->toBe('ñiño');
        });

        test('replaces special characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast('@', '[at]')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'email@example.com']);

            expect($data->field)->toBe('email[at]example.com');
        });

        test('handles case-sensitive replacement', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast('Hello', 'Hi')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello hello HELLO']);

            expect($data->field)->toBe('Hi hello HELLO');
        });

        test('returns non-string values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast('test', 'demo')]
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
                    #[ReplaceCast('test', 'demo')]
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
                    #[ReplaceCast('test', 'demo')]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['test']]);

            expect($data->field)->toBe(['test']);
        });

        test('handles whitespace in search and replace', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast(' ', '-')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'hello world test']);

            expect($data->field)->toBe('hello-world-test');
        });

        test('replaces newlines and tabs', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[ReplaceCast("\n", ' ')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => "line1\nline2\nline3"]);

            expect($data->field)->toBe('line1 line2 line3');
        });
    });
});
