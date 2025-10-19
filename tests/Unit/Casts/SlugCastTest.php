<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\SlugCast;
use Spatie\LaravelData\Data;

describe('SlugCast', function (): void {
    describe('Happy Paths', function (): void {
        test('converts string to slug with default separator', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe('hello-world');
        });

        test('converts string to slug with custom separator', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast(separator: '_')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe('hello_world');
        });

        test('handles empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '']);

            expect($data->field)->toBe('');
        });

        test('removes special characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello!@# World']);

            expect($data->field)->toBe('hello-world');
        });

        test('handles multiple spaces', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello   World']);

            expect($data->field)->toBe('hello-world');
        });
    });

    describe('Edge Cases', function (): void {
        test('returns non-string values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast()]
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
                    #[SlugCast()]
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
                    #[SlugCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['test']]);

            expect($data->field)->toBe(['test']);
        });

        test('handles unicode characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Ñoño Español']);

            expect($data->field)->toBe('ñoño-español');
        });

        test('handles already slugified strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'already-slugified']);

            expect($data->field)->toBe('already-slugified');
        });

        test('handles strings with numbers', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Product 123 Test']);

            expect($data->field)->toBe('product-123-test');
        });

        test('handles custom language with dictionary', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast(dictionary: ['&' => 'and'])]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello & World']);

            expect($data->field)->toBe('hello-and-world');
        });

        test('handles leading and trailing spaces', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '  Hello World  ']);

            expect($data->field)->toBe('hello-world');
        });

        test('handles consecutive special characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello!!! @@@ World']);

            expect($data->field)->toBe('hello-world');
        });

        test('handles mixed case with numbers', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[SlugCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'PHP8 Is Awesome']);

            expect($data->field)->toBe('php8-is-awesome');
        });
    });
});
