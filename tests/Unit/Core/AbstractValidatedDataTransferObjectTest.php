<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Core\AbstractDataTransferObject;
use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\Dto;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Tests\Fixtures\ConcreteValidatedDataTransferObject;

describe('AbstractValidatedDataTransferObject', function (): void {
    describe('Happy Paths', function (): void {
        test('creates instance with valid data passing all validations', function (): void {
            // Arrange
            $data = [
                'productName' => 'Laptop',
                'price' => 999.99,
                'quantity' => 5,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->productName)->toBe('Laptop');
            expect($result->price)->toBe(999.99);
            expect($result->quantity)->toBe(5);
            expect($result->sku)->toBeNull();
        });

        test('creates instance with optional sku property', function (): void {
            // Arrange
            $data = [
                'productName' => 'Laptop',
                'price' => 999.99,
                'quantity' => 5,
                'sku' => 'LAP-001',
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->productName)->toBe('Laptop');
            expect($result->price)->toBe(999.99);
            expect($result->quantity)->toBe(5);
            expect($result->sku)->toBe('LAP-001');
        });

        test('validates minimum price constraint', function (): void {
            // Arrange
            $data = [
                'productName' => 'Free Item',
                'price' => 0.0,  // Minimum allowed
                'quantity' => 1,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->price)->toBe(0.0);
        });

        test('validates minimum quantity constraint', function (): void {
            // Arrange
            $data = [
                'productName' => 'Product',
                'price' => 10.0,
                'quantity' => 1,  // Minimum allowed
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->quantity)->toBe(1);
        });

        test('properties are accessible after validation', function (): void {
            // Arrange
            $validatedDto = ConcreteValidatedDataTransferObject::from([
                'productName' => 'Laptop',
                'price' => 999.99,
                'quantity' => 5,
            ]);

            // Act & Assert
            expect($validatedDto->productName)->toBe('Laptop');
            expect($validatedDto->price)->toBe(999.99);
            expect($validatedDto->quantity)->toBe(5);
            expect($validatedDto->sku)->toBeNull();
        });

        test('validates with data pipeline transformations', function (): void {
            // Arrange
            $data = [
                'productName' => 123,  // Will be cast to string
                'price' => '999.99',  // Will be cast to float
                'quantity' => '5',  // Will be cast to int
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->productName)->toBe('123');
            expect($result->price)->toBe(999.99);
            expect($result->quantity)->toBe(5);
        });

        test('replaces empty strings with null during validation', function (): void {
            // Arrange
            $data = [
                'productName' => 'Laptop',
                'price' => 999.99,
                'quantity' => 5,
                'sku' => '',  // Empty string should become null
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->sku)->toBeNull();
        });

        test('creates collection of validated data transfer objects', function (): void {
            // Arrange
            $dataArray = [
                ['productName' => 'Product 1', 'price' => 10.0, 'quantity' => 1],
                ['productName' => 'Product 2', 'price' => 20.0, 'quantity' => 2],
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::collect($dataArray);

            // Assert
            expect($result)->toHaveCount(2);
            expect($result[0]->productName)->toBe('Product 1');
            expect($result[1]->productName)->toBe('Product 2');
        });

        test('inherits from AbstractDataTransferObject base class', function (): void {
            // Arrange
            $dto = ConcreteValidatedDataTransferObject::from([
                'productName' => 'Laptop',
                'price' => 999.99,
                'quantity' => 5,
            ]);

            // Act & Assert
            expect($dto)->toBeInstanceOf(AbstractDataTransferObject::class);
        });

        test('inherits from Dto base class', function (): void {
            // Arrange
            $dto = ConcreteValidatedDataTransferObject::from([
                'productName' => 'Laptop',
                'price' => 999.99,
                'quantity' => 5,
            ]);

            // Act & Assert
            expect($dto)->toBeInstanceOf(Dto::class);
        });

        test('uses validation strategy always', function (): void {
            // Arrange & Act
            $factory = ConcreteValidatedDataTransferObject::factory();

            // Assert
            expect($factory)->toBeInstanceOf(CreationContextFactory::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('fails validation when required productName is missing', function (): void {
            // Arrange
            $data = [
                'price' => 999.99,
                'quantity' => 5,
                // Missing 'productName'
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedDataTransferObject => ConcreteValidatedDataTransferObject::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when required price is missing', function (): void {
            // Arrange
            $data = [
                'productName' => 'Laptop',
                'quantity' => 5,
                // Missing 'price'
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedDataTransferObject => ConcreteValidatedDataTransferObject::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when required quantity is missing', function (): void {
            // Arrange
            $data = [
                'productName' => 'Laptop',
                'price' => 999.99,
                // Missing 'quantity'
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedDataTransferObject => ConcreteValidatedDataTransferObject::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when price is negative', function (): void {
            // Arrange
            $data = [
                'productName' => 'Laptop',
                'price' => -10.0,  // Below minimum of 0
                'quantity' => 5,
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedDataTransferObject => ConcreteValidatedDataTransferObject::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when quantity is zero', function (): void {
            // Arrange
            $data = [
                'productName' => 'Laptop',
                'price' => 999.99,
                'quantity' => 0,  // Below minimum of 1
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedDataTransferObject => ConcreteValidatedDataTransferObject::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when quantity is negative', function (): void {
            // Arrange
            $data = [
                'productName' => 'Laptop',
                'price' => 999.99,
                'quantity' => -5,  // Below minimum of 1
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedDataTransferObject => ConcreteValidatedDataTransferObject::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when price is cast to zero from non-numeric string', function (): void {
            // Arrange
            $data = [
                'productName' => 'Laptop',
                'price' => 'not-a-number',  // Will be cast to 0.0, which passes Min(0)
                'quantity' => 5,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->price)->toBeFloat();
        });

        test('fails validation when quantity is cast to zero from non-numeric string', function (): void {
            // Arrange
            $data = [
                'productName' => 'Laptop',
                'price' => 999.99,
                'quantity' => 'five',  // Will be cast to 0, which fails Min(1)
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedDataTransferObject => ConcreteValidatedDataTransferObject::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when multiple fields are invalid', function (): void {
            // Arrange
            $data = [
                'productName' => 'Laptop',
                'price' => -10.0,  // Invalid
                'quantity' => 0,  // Invalid
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedDataTransferObject => ConcreteValidatedDataTransferObject::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when all required fields are missing', function (): void {
            // Arrange
            $data = [];

            // Act & Assert
            expect(fn (): ConcreteValidatedDataTransferObject => ConcreteValidatedDataTransferObject::from($data))
                ->toThrow(Exception::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('handles unicode characters in validated string fields', function (): void {
            // Arrange
            $data = [
                'productName' => 'Portátil (Laptop)',
                'price' => 999.99,
                'quantity' => 5,
                'sku' => 'SKU-ñoño-日本',
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->productName)->toBe('Portátil (Laptop)');
            expect($result->sku)->toBe('SKU-ñoño-日本');
        });

        test('handles very large price values', function (): void {
            // Arrange
            $data = [
                'productName' => 'Expensive Item',
                'price' => 999_999_999.99,
                'quantity' => 1,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->price)->toBe(999_999_999.99);
        });

        test('handles very large quantity values', function (): void {
            // Arrange
            $data = [
                'productName' => 'Bulk Item',
                'price' => 1.0,
                'quantity' => \PHP_INT_MAX,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->quantity)->toBe(\PHP_INT_MAX);
        });

        test('handles decimal precision for price', function (): void {
            // Arrange
            $data = [
                'productName' => 'Product',
                'price' => 19.999,  // Three decimal places
                'quantity' => 1,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->price)->toBe(19.999);
        });

        test('handles whitespace in optional sku field', function (): void {
            // Arrange
            $data = [
                'productName' => 'Product',
                'price' => 10.0,
                'quantity' => 1,
                'sku' => '   ',  // Whitespace should become null
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->sku)->toBeNull();
        });

        test('handles special characters in product name', function (): void {
            // Arrange
            $data = [
                'productName' => 'Product "Special" & <Edition>',
                'price' => 50.0,
                'quantity' => 1,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->productName)->toBe('Product "Special" & <Edition>');
        });

        test('handles very long product name', function (): void {
            // Arrange
            $longName = str_repeat('Product Name ', 50);
            $data = [
                'productName' => $longName,
                'price' => 10.0,
                'quantity' => 1,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->productName)->toBe($longName);
        });

        test('handles very long sku', function (): void {
            // Arrange
            $longSku = str_repeat('ABC', 100);
            $data = [
                'productName' => 'Product',
                'price' => 10.0,
                'quantity' => 1,
                'sku' => $longSku,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->sku)->toBe($longSku);
        });

        test('creates multiple validated instances independently', function (): void {
            // Arrange
            $data1 = ['productName' => 'Product 1', 'price' => 10.0, 'quantity' => 1];
            $data2 = ['productName' => 'Product 2', 'price' => 20.0, 'quantity' => 2];

            // Act
            $result1 = ConcreteValidatedDataTransferObject::from($data1);
            $result2 = ConcreteValidatedDataTransferObject::from($data2);

            // Assert
            expect($result1->productName)->toBe('Product 1');
            expect($result2->productName)->toBe('Product 2');
            expect($result1)->not->toBe($result2);
        });

        test('validates with json string input', function (): void {
            // Arrange
            $json = json_encode([
                'productName' => 'Laptop',
                'price' => 999.99,
                'quantity' => 5,
            ]);

            // Act
            $result = ConcreteValidatedDataTransferObject::from($json);

            // Assert
            expect($result->productName)->toBe('Laptop');
            expect($result->price)->toBe(999.99);
            expect($result->quantity)->toBe(5);
        });

        test('validates with object input', function (): void {
            // Arrange
            $object = new stdClass();
            $object->productName = 'Laptop';
            $object->price = 999.99;
            $object->quantity = 5;

            // Act
            $result = ConcreteValidatedDataTransferObject::from($object);

            // Assert
            expect($result->productName)->toBe('Laptop');
            expect($result->price)->toBe(999.99);
            expect($result->quantity)->toBe(5);
        });

        test('validates boundary price value', function (): void {
            // Arrange
            $data = [
                'productName' => 'Free Product',
                'price' => 0.0,  // Boundary value
                'quantity' => 1,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->price)->toBe(0.0);
        });

        test('validates boundary quantity value', function (): void {
            // Arrange
            $data = [
                'productName' => 'Product',
                'price' => 10.0,
                'quantity' => 1,  // Boundary value
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->quantity)->toBe(1);
        });

        test('verifies pipeline configuration is inherited from AbstractDataTransferObject', function (): void {
            // Arrange & Act
            $pipeline = ConcreteValidatedDataTransferObject::pipeline();

            // Assert
            expect($pipeline)->toBeInstanceOf(DataPipeline::class);
        });

        test('handles alphanumeric sku values', function (): void {
            // Arrange
            $data = [
                'productName' => 'Product',
                'price' => 10.0,
                'quantity' => 1,
                'sku' => 'ABC-123-XYZ-789',
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->sku)->toBe('ABC-123-XYZ-789');
        });

        test('handles price with many decimal places', function (): void {
            // Arrange
            $data = [
                'productName' => 'Product',
                'price' => 10.123_456_789,
                'quantity' => 1,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->price)->toBe(10.123_456_789);
        });

        test('validates with trimmed whitespace in product name', function (): void {
            // Arrange
            $data = [
                'productName' => '  Laptop  ',
                'price' => 999.99,
                'quantity' => 5,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->productName)->toBe('  Laptop  ');
        });

        test('handles floating point precision edge case', function (): void {
            // Arrange
            $data = [
                'productName' => 'Product',
                'price' => 0.1 + 0.2,  // Classic floating point issue
                'quantity' => 1,
            ];

            // Act
            $result = ConcreteValidatedDataTransferObject::from($data);

            // Assert
            expect($result->price)->toBeFloat();
        });
    });
});
