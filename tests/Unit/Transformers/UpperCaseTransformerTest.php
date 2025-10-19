<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Transformers\UpperCaseTransformer;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;

describe('UpperCaseTransformer', function (): void {
    describe('Happy Paths', function (): void {
        test('transforms lowercase string to uppercase', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $name = null,
                ) {}
            };

            $data = $dataClass::from(['name' => 'john doe']);
            $result = $data->toArray();

            expect($result['name'])->toBe('JOHN DOE');
        });

        test('transforms mixed case string to uppercase', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            $data = $dataClass::from(['text' => 'HeLLo WoRLd']);
            $result = $data->toArray();

            expect($result['text'])->toBe('HELLO WORLD');
        });

        test('leaves uppercase string unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            $data = $dataClass::from(['text' => 'ALREADY UPPERCASE']);
            $result = $data->toArray();

            expect($result['text'])->toBe('ALREADY UPPERCASE');
        });

        test('transforms alphanumeric strings correctly', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $code = null,
                ) {}
            };

            $data = $dataClass::from(['code' => 'abc123xyz']);
            $result = $data->toArray();

            expect($result['code'])->toBe('ABC123XYZ');
        });

        test('transforms multiple properties independently', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $firstName = null,
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $lastName = null,
                ) {}
            };

            $data = $dataClass::from(['firstName' => 'john', 'lastName' => 'doe']);
            $result = $data->toArray();

            expect($result['firstName'])->toBe('JOHN')
                ->and($result['lastName'])->toBe('DOE');
        });
    });

    describe('Sad Paths', function (): void {
        test('returns non-string values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?int $number = null,
                ) {}
            };

            $data = $dataClass::from(['number' => 12_345]);
            $result = $data->toArray();

            expect($result['number'])->toBe(12_345);
        });

        test('returns null unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::from(['value' => null]);
            $result = $data->toArray();

            expect($result['value'])->toBeNull();
        });

        test('returns boolean unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly bool $flag = false,
                ) {}
            };

            $data = $dataClass::from(['flag' => false]);
            $result = $data->toArray();

            expect($result['flag'])->toBeFalse();
        });

        test('returns float unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?float $price = null,
                ) {}
            };

            $data = $dataClass::from(['price' => 99.99]);
            $result = $data->toArray();

            expect($result['price'])->toBe(99.99);
        });

        test('returns array unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?array $items = null,
                ) {}
            };

            $data = $dataClass::from(['items' => ['lowercase', 'values']]);
            $result = $data->toArray();

            expect($result['items'])->toBe(['lowercase', 'values']);
        });
    });

    describe('Edge Cases', function (): void {
        test('transforms empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::from(['value' => '']);
            $result = $data->toArray();

            expect($result['value'])->toBe('');
        });

        test('transforms unicode characters correctly', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            $data = $dataClass::from(['text' => 'ñoño']);
            $result = $data->toArray();

            expect($result['text'])->toBe('ÑOÑO');
        });

        test('transforms accented characters correctly', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            $data = $dataClass::from(['text' => 'àéîöü']);
            $result = $data->toArray();

            expect($result['text'])->toBe('ÀÉÎÖÜ');
        });

        test('transforms cyrillic characters correctly', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            $data = $dataClass::from(['text' => 'привет']);
            $result = $data->toArray();

            expect($result['text'])->toBe('ПРИВЕТ');
        });

        test('transforms greek characters correctly', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            $data = $dataClass::from(['text' => 'γεια σου']);
            $result = $data->toArray();

            expect($result['text'])->toBe('ΓΕΙΑ ΣΟΥ');
        });

        test('handles special characters correctly', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            $data = $dataClass::from(['text' => 'hello! @world# $test%']);
            $result = $data->toArray();

            expect($result['text'])->toBe('HELLO! @WORLD# $TEST%');
        });

        test('preserves emojis', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            $data = $dataClass::from(['text' => 'hello 😀 world']);
            $result = $data->toArray();

            expect($result['text'])->toBe('HELLO 😀 WORLD');
        });

        test('transforms very long strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(UpperCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            $longString = str_repeat('a', 1_000);
            $data = $dataClass::from(['text' => $longString]);
            $result = $data->toArray();

            expect($result['text'])->toBe(str_repeat('A', 1_000))
                ->and(mb_strlen((string) $result['text']))->toBe(1_000);
        });
    });
});
