<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\UrlEncodeCast;
use Spatie\LaravelData\Data;

describe('UrlEncodeCast', function (): void {
    describe('Happy Paths', function (): void {
        test('encodes string with spaces', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe('Hello+World');
        });

        test('encodes string with special characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'test@example.com']);
            expect($data->field)->toBe('test%40example.com');
        });

        test('encodes empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '']);
            expect($data->field)->toBe('');
        });

        test('encodes symbols', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '!@#$%']);
            expect($data->field)->toBe('%21%40%23%24%25');
        });

        test('encodes query string format', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'key=value&other=thing']);
            expect($data->field)->toBe('key%3Dvalue%26other%3Dthing');
        });

        test('encodes path with slashes', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'path/to/resource']);
            expect($data->field)->toBe('path%2Fto%2Fresource');
        });
    });

    describe('Edge Cases', function (): void {
        test('returns non-string values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
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
                    #[UrlEncodeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from([]);
            expect($data->field)->toBeNull();
        });

        test('returns array unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['test value']]);
            expect($data->field)->toBe(['test value']);
        });

        test('handles already encoded strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello%20World']);
            expect($data->field)->toBe('Hello%2520World');
        });

        test('handles multibyte characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Ñoño']);
            expect($data->field)->toBe('%C3%91o%C3%B1o');
        });

        test('handles unicode characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '日本語']);
            expect($data->field)->toBe('%E6%97%A5%E6%9C%AC%E8%AA%9E');
        });

        test('handles alphanumeric characters unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'abc123XYZ']);
            expect($data->field)->toBe('abc123XYZ');
        });

        test('handles hyphens and underscores unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'test-value_name']);
            expect($data->field)->toBe('test-value_name');
        });

        test('handles periods and tildes unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'file.txt~backup']);
            expect($data->field)->toBe('file.txt%7Ebackup');
        });

        test('handles hash fragments', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'page#section']);
            expect($data->field)->toBe('page%23section');
        });

        test('handles ampersands', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'param1&param2']);
            expect($data->field)->toBe('param1%26param2');
        });

        test('handles question marks', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'search?query']);
            expect($data->field)->toBe('search%3Fquery');
        });

        test('handles quotes', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'test "quoted" value']);
            expect($data->field)->toBe('test+%22quoted%22+value');
        });

        test('encodes and decodes correctly', function (): void {
            $original = 'Hello World!@#';

            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlEncodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $encodeData = $dataClass::from(['field' => $original]);
            $encoded = $encodeData->field;

            expect($encoded)->toBe('Hello+World%21%40%23');
            expect(urldecode($encoded))->toBe($original);
        });
    });
});
