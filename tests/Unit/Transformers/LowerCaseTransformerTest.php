<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Transformers\LowerCaseTransformer;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;

describe('LowerCaseTransformer', function (): void {
    describe('Happy Paths', function (): void {
        test('transforms uppercase string to lowercase', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $name = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['name' => 'JOHN DOE']);

            // Assert
            $result = $data->toArray();
            expect($result['name'])->toBe('john doe');
        });

        test('transforms mixed case string to lowercase', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => 'HeLLo WoRLd']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('hello world');
        });

        test('leaves lowercase string unchanged', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => 'already lowercase']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('already lowercase');
        });

        test('transforms string with numbers and lowercase letters', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $code = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['code' => 'ABC123def']);

            // Assert
            $result = $data->toArray();
            expect($result['code'])->toBe('abc123def');
        });

        test('transforms multiple string properties', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $firstName = null,
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $lastName = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['firstName' => 'JOHN', 'lastName' => 'DOE']);

            // Assert
            $result = $data->toArray();
            expect($result['firstName'])->toBe('john')
                ->and($result['lastName'])->toBe('doe');
        });
    });

    describe('Sad Paths', function (): void {
        test('returns non-string values unchanged', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?int $number = null,
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly bool $bool = false,
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?float $float = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['number' => 123, 'bool' => true, 'float' => 45.67]);

            // Assert
            $result = $data->toArray();
            expect($result['number'])->toBe(123)
                ->and($result['bool'])->toBeTrue()
                ->and($result['float'])->toBe(45.67);
        });

        test('returns null value unchanged', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly mixed $value = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['value' => null]);

            // Assert
            $result = $data->toArray();
            expect($result['value'])->toBeNull();
        });

        test('returns array unchanged', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?array $items = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['items' => ['APPLE', 'BANANA']]);

            // Assert
            $result = $data->toArray();
            expect($result['items'])->toBe(['APPLE', 'BANANA']);
        });
    });

    describe('Edge Cases', function (): void {
        test('transforms empty string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => '']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('');
        });

        test('transforms single character uppercase', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $char = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['char' => 'A']);

            // Assert
            $result = $data->toArray();
            expect($result['char'])->toBe('a');
        });

        test('transforms unicode characters correctly', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => 'ÑOÑO']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('ñoño');
        });

        test('transforms string with accented characters', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => 'CAFÉ MÜNSTER']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('café münster');
        });

        test('transforms cyrillic characters', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => 'ПРИВЕТ']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('привет');
        });

        test('transforms greek characters', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => 'ΑΛΦΑ']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('αλφα');
        });

        test('transforms string with special characters and punctuation', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => 'HELLO, WORLD! HOW ARE YOU?']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('hello, world! how are you?');
        });

        test('transforms string with whitespace characters', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => "HELLO\tWORLD\nNEW LINE"]);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe("hello\tworld\nnew line");
        });

        test('transforms string with emojis', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => 'HELLO 🎉 WORLD 🚀']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('hello 🎉 world 🚀');
        });

        test('transforms very long string', function (): void {
            // Arrange
            $longString = str_repeat('ABCDEFGHIJ', 1_000);
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => $longString]);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe(str_repeat('abcdefghij', 1_000))
                ->and(mb_strlen((string) $result['text']))->toBe(10_000);
        });

        test('preserves numbers in string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(LowerCaseTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => '12345']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('12345');
        });
    });
});
