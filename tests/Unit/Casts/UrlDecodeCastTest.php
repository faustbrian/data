<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\UrlDecodeCast;
use Spatie\LaravelData\Data;

describe('UrlDecodeCast', function (): void {
    describe('Happy Paths', function (): void {
        test('decodes URL encoded string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello%20World']);

            expect($data->field)->toBe('Hello World');
        });

        test('decodes URL with special characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'test%40example.com']);

            expect($data->field)->toBe('test@example.com');
        });

        test('decodes URL with plus signs as spaces', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello+World']);

            expect($data->field)->toBe('Hello World');
        });

        test('decodes empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '']);

            expect($data->field)->toBe('');
        });

        test('decodes URL with percent encoded symbols', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '%21%40%23%24%25']);

            expect($data->field)->toBe('!@#$%');
        });

        test('decodes URL with query string format', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'key%3Dvalue%26other%3Dthing']);

            expect($data->field)->toBe('key=value&other=thing');
        });
    });

    describe('Edge Cases', function (): void {
        test('returns non-string values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
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
                    #[UrlDecodeCast()]
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
                    #[UrlDecodeCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['test%20value']]);

            expect($data->field)->toBe(['test%20value']);
        });

        test('handles already decoded strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe('Hello World');
        });

        test('handles multibyte characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '%C3%91o%C3%B1o']);

            expect($data->field)->toBe('Ñoño');
        });

        test('handles unicode characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '%E6%97%A5%E6%9C%AC%E8%AA%9E']);

            expect($data->field)->toBe('日本語');
        });

        test('handles URL path with slashes', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'path%2Fto%2Fresource']);

            expect($data->field)->toBe('path/to/resource');
        });

        test('handles malformed percent encoding', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'test%2']);

            expect($data->field)->toBe('test%2');
        });

        test('handles double encoded strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello%2520World']);

            expect($data->field)->toBe('Hello%20World');
        });

        test('handles URL with hash fragment', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'page%23section']);

            expect($data->field)->toBe('page#section');
        });

        test('handles URL with ampersands', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UrlDecodeCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'param1%26param2']);

            expect($data->field)->toBe('param1&param2');
        });
    });
});
