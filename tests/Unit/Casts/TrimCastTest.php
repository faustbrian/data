<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\TrimCast;
use Spatie\LaravelData\Data;

describe('TrimCast', function (): void {
    describe('Happy Paths', function (): void {
        test('trims leading whitespace', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '  Hello World']);

            expect($data->field)->toBe('Hello World');
        });

        test('trims trailing whitespace', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World  ']);

            expect($data->field)->toBe('Hello World');
        });

        test('trims both leading and trailing whitespace', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '  Hello World  ']);

            expect($data->field)->toBe('Hello World');
        });

        test('handles empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '']);

            expect($data->field)->toBe('');
        });

        test('trims string with only whitespace', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '   ']);

            expect($data->field)->toBe('');
        });

        test('preserves internal whitespace', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '  Hello    World  ']);

            expect($data->field)->toBe('Hello    World');
        });
    });

    describe('Edge Cases', function (): void {
        test('returns non-string values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
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
                    #[TrimCast()]
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
                    #[TrimCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['  test  ']]);

            expect($data->field)->toBe(['  test  ']);
        });

        test('trims tabs', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => "\tHello World\t"]);

            expect($data->field)->toBe('Hello World');
        });

        test('trims newlines', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => "\nHello World\n"]);

            expect($data->field)->toBe('Hello World');
        });

        test('trims carriage returns', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => "\rHello World\r"]);

            expect($data->field)->toBe('Hello World');
        });

        test('trims null bytes', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => "\0Hello World\0"]);

            expect($data->field)->toBe('Hello World');
        });

        test('trims vertical tabs', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => "\x0BHello World\x0B"]);

            expect($data->field)->toBe('Hello World');
        });

        test('handles multibyte characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '  Ñoño  ']);

            expect($data->field)->toBe('Ñoño');
        });

        test('handles unicode characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '  日本語  ']);

            expect($data->field)->toBe('日本語');
        });

        test('trims custom characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast(characters: '#')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '###Hello World###']);

            expect($data->field)->toBe('Hello World');
        });

        test('trims multiple custom characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast(characters: '@#')]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '@#@#Hello World#@#@']);

            expect($data->field)->toBe('Hello World');
        });

        test('trims mixed whitespace types', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => " \t\n\rHello World\r\n\t "]);

            expect($data->field)->toBe('Hello World');
        });

        test('handles already trimmed string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[TrimCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe('Hello World');
        });
    });
});
