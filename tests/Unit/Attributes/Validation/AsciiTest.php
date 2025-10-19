<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\Ascii;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;

describe('Ascii', function (): void {
    describe('Happy Paths', function (): void {
        test('validates ascii string successfully', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $name = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['name' => 'John Doe']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates string with numbers and symbols', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['value' => 'test123!@#']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $text = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['text' => '']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates string with spaces and tabs', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $content = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['content' => "hello\tworld test"]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects string with unicode characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $name = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['name' => 'Ñoño']))
                ->toThrow(ValidationException::class);
        });

        test('rejects string with emoji', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $text = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['text' => 'Hello 😀']))
                ->toThrow(ValidationException::class);
        });

        test('rejects string with cyrillic characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $text = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['text' => 'Привет']))
                ->toThrow(ValidationException::class);
        });

        test('rejects string with chinese characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $text = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['text' => '你好']))
                ->toThrow(ValidationException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('validates string with all printable ascii characters', function (): void {
            $ascii = '';

            for ($i = 32; $i <= 126; ++$i) {
                $ascii .= chr($i);
            }

            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $text = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['text' => $ascii]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates string with newlines and carriage returns', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $content = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['content' => "line1\nline2\rline3"]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates string with allowed control characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $text = null,
                ) {}
            };

            // Allowed control chars: \x09 (TAB), \x0A (LF), \x0D (CR), \x10 (DLE), \x13 (DC3)
            $data = $dataClass::validateAndCreate(['text' => "\x09\x0A\x0D"]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('rejects string with disallowed control characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $text = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['text' => "\x00\x01\x02\x1F"]))
                ->toThrow(ValidationException::class);
        });

        test('rejects mixed ascii and non-ascii', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Ascii()]
                    public readonly ?string $text = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['text' => 'Hello Wörld']))
                ->toThrow(ValidationException::class);
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new Ascii();

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('returns correct keyword', function (): void {
            expect(Ascii::keyword())->toBe('ascii');
        });

        test('returns empty parameters array', function (): void {
            $attribute = new Ascii();

            expect($attribute->parameters())->toBe([]);
        });
    });
});
