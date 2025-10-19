<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\Base64Cast;
use Spatie\LaravelData\Data;

describe('Base64Cast', function (): void {
    describe('Happy Paths - Decoding', function (): void {
        test('decodes valid base64 string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Base64Cast(decode: true)]
                    public readonly string $field = '',
                ) {}
            };

            $encoded = base64_encode('Hello World');
            $data = $dataClass::from(['field' => $encoded]);

            expect($data->field)->toBe('Hello World');
        });

        test('decodes base64 with special characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Base64Cast(decode: true)]
                    public readonly string $field = '',
                ) {}
            };

            $original = 'Test with symbols: !@#$%^&*()';
            $encoded = base64_encode($original);
            $data = $dataClass::from(['field' => $encoded]);

            expect($data->field)->toBe($original);
        });

        test('decodes empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Base64Cast(decode: true)]
                    public readonly string $field = '',
                ) {}
            };

            $encoded = base64_encode('');
            $data = $dataClass::from(['field' => $encoded]);

            expect($data->field)->toBe('');
        });
    });

    describe('Happy Paths - Encoding', function (): void {
        test('encodes string to base64', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Base64Cast(decode: false)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe(base64_encode('Hello World'));
        });

        test('encodes empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Base64Cast(decode: false)]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '']);

            expect($data->field)->toBe('');
        });

        test('encodes string with special characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Base64Cast(decode: false)]
                    public readonly string $field = '',
                ) {}
            };

            $original = 'Test: !@#$%';
            $data = $dataClass::from(['field' => $original]);

            expect($data->field)->toBe(base64_encode($original));
        });
    });

    describe('Edge Cases', function (): void {
        test('returns non-string values unchanged when decoding', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Base64Cast(decode: true)]
                    public readonly int $field = 0,
                ) {}
            };

            $data = $dataClass::from(['field' => 123]);

            expect($data->field)->toBe(123);
        });

        test('returns non-string values unchanged when encoding', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[Base64Cast(decode: false)]
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
                    #[Base64Cast(decode: true)]
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
                    #[Base64Cast(decode: true)]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['test']]);

            expect($data->field)->toBe(['test']);
        });

        test('handles multibyte characters', function (): void {
            $original = 'Ñoño 日本語';

            // Encode
            $encodeClass = new class() extends Data
            {
                public function __construct(
                    #[Base64Cast(decode: false)]
                    public readonly string $field = '',
                ) {}
            };

            $encodeData = $encodeClass::from(['field' => $original]);

            // Decode
            $decodeClass = new class() extends Data
            {
                public function __construct(
                    #[Base64Cast(decode: true)]
                    public readonly string $field = '',
                ) {}
            };

            $decodeData = $decodeClass::from(['field' => $encodeData->field]);

            expect($decodeData->field)->toBe($original);
        });

        test('strict mode throws exception for invalid base64', function (): void {
            expect(function (): void {
                $dataClass = new class() extends Data
                {
                    public function __construct(
                        #[Base64Cast(decode: true, strict: true)]
                        public readonly string $field = '',
                    ) {}
                };

                $dataClass::from(['field' => 'invalid base64!']);
            })->toThrow(InvalidArgumentException::class, 'Invalid base64 string');
        });
    });
});
