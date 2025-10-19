<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Transformers\JsonStringTransformer;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;

describe('JsonStringTransformer', function (): void {
    describe('Happy Paths', function (): void {
        test('transforms simple array to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?array $items = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['items' => ['apple', 'banana', 'cherry']]);

            // Assert
            $result = $data->toArray();
            expect($result['items'])->toBeString()
                ->and($result['items'])->toBe('["apple","banana","cherry"]');
        });

        test('transforms associative array to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?array $config = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['config' => ['host' => 'localhost', 'port' => 3_306]]);

            // Assert
            $result = $data->toArray();
            expect($result['config'])->toBeString()
                ->and($result['config'])->toBe('{"host":"localhost","port":3306}');
        });

        test('transforms nested array structure to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?array $data = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['data' => ['user' => ['name' => 'John', 'roles' => ['admin', 'user']]]]);

            // Assert
            $result = $data->toArray();
            expect($result['data'])->toBeString()
                ->and($result['data'])->toBe('{"user":{"name":"John","roles":["admin","user"]}}');
        });

        test('transforms string value to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => 'Hello World']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('"Hello World"');
        });

        test('transforms numeric value to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?int $number = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['number' => 42]);

            // Assert
            $result = $data->toArray();
            expect($result['number'])->toBe('42');
        });

        test('transforms boolean values to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly bool $isActive = false,
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly bool $isDeleted = false,
                ) {}
            };

            // Act
            $data = $dataClass::from(['isActive' => true, 'isDeleted' => false]);

            // Assert
            $result = $data->toArray();
            expect($result['isActive'])->toBe('true')
                ->and($result['isDeleted'])->toBe('false');
        });

        test('preserves slashes in url when using unescaped slashes flag', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?string $url = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['url' => 'https://example.com/path/to/resource']);

            // Assert
            $result = $data->toArray();
            expect($result['url'])->toBe('"https://example.com/path/to/resource"')
                ->and($result['url'])->not->toContain('\\/');
        });

        test('preserves unicode characters when using unescaped unicode flag', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => 'Ñoño 日本語 🎉']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('"Ñoño 日本語 🎉"')
                ->and($result['text'])->toContain('日本語');
        });
    });

    describe('Sad Paths', function (): void {
        test('throws exception when encoding fails for invalid utf8', function (): void {
            // Arrange
            $invalidUtf8 = "\xB1\x31";
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act & Assert
            expect(fn (): array => $dataClass::from(['text' => $invalidUtf8])->toArray())
                ->toThrow(InvalidArgumentException::class, 'Failed to encode value as JSON');
        });
    });

    describe('Edge Cases', function (): void {
        test('transforms empty array to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?array $items = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['items' => []]);

            // Assert
            $result = $data->toArray();
            expect($result['items'])->toBe('[]');
        });

        test('transforms null value to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly mixed $value = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['value' => null]);

            // Assert
            $result = $data->toArray();
            expect($result['value'])->toBeNull();
        });

        test('transforms empty string to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => '']);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('""');
        });

        test('transforms special characters to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?string $text = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['text' => "Line 1\nLine 2\tTabbed"]);

            // Assert
            $result = $data->toArray();
            expect($result['text'])->toBe('"Line 1\nLine 2\tTabbed"');
        });

        test('transforms array with numeric keys to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?array $items = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['items' => [1 => 'first', 5 => 'fifth', 10 => 'tenth']]);

            // Assert
            $result = $data->toArray();
            expect($result['items'])->toBeString()
                ->and($result['items'])->toContain('first')
                ->and($result['items'])->toContain('fifth');
        });

        test('transforms deeply nested structure to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?array $deep = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['deep' => ['level1' => ['level2' => ['level3' => ['level4' => 'value']]]]]);

            // Assert
            $result = $data->toArray();
            expect($result['deep'])->toBeString()
                ->and($result['deep'])->toContain('level4')
                ->and($result['deep'])->toContain('value');
        });

        test('transforms float with precision to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?float $price = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['price' => 19.99]);

            // Assert
            $result = $data->toArray();
            expect($result['price'])->toBe('19.99');
        });

        test('transforms large numbers to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?int $bigNumber = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['bigNumber' => 9_223_372_036_854_775_807]);

            // Assert
            $result = $data->toArray();
            expect($result['bigNumber'])->toBe('9223372036854775807');
        });

        test('transforms zero values to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?int $zero = null,
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?float $zeroFloat = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['zero' => 0, 'zeroFloat' => 0.0]);

            // Assert
            $result = $data->toArray();
            expect($result['zero'])->toBe('0')
                ->and($result['zeroFloat'])->toBe('0');
        });

        test('transforms mixed type array to json string', function (): void {
            // Arrange
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(JsonStringTransformer::class)]
                    public readonly ?array $mixed = null,
                ) {}
            };

            // Act
            $data = $dataClass::from(['mixed' => [1, 'text', true, null, ['nested' => 'value']]]);

            // Assert
            $result = $data->toArray();
            expect($result['mixed'])->toBeString()
                ->and($result['mixed'])->toContain('text')
                ->and($result['mixed'])->toContain('nested');
        });
    });
});
