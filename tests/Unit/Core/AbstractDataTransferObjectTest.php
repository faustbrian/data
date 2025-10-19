<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\Dto;
use Tests\Fixtures\ConcreteDataTransferObject;

describe('AbstractDataTransferObject', function (): void {
    describe('Happy Paths', function (): void {
        test('creates instance with all required properties', function (): void {
            // Arrange
            $data = ['title' => 'Test Title', 'count' => 5];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->title)->toBe('Test Title');
            expect($result->count)->toBe(5);
            expect($result->description)->toBeNull();
            expect($result->enabled)->toBeFalse();
            expect($result->price)->toBeNull();
            expect($result->metadata)->toBeNull();
        });

        test('creates instance with all properties including optional', function (): void {
            // Arrange
            $data = [
                'title' => 'Product Title',
                'count' => 10,
                'description' => 'A detailed description',
                'enabled' => true,
                'price' => 99.99,
                'metadata' => ['category' => 'electronics', 'brand' => 'TechCo'],
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->title)->toBe('Product Title');
            expect($result->count)->toBe(10);
            expect($result->description)->toBe('A detailed description');
            expect($result->enabled)->toBeTrue();
            expect($result->price)->toBe(99.99);
            expect($result->metadata)->toBe(['category' => 'electronics', 'brand' => 'TechCo']);
        });

        test('properties are accessible after instantiation', function (): void {
            // Arrange
            $dto = ConcreteDataTransferObject::from([
                'title' => 'Test Title',
                'count' => 5,
                'description' => 'Test Description',
            ]);

            // Act & Assert
            expect($dto->title)->toBe('Test Title');
            expect($dto->count)->toBe(5);
            expect($dto->description)->toBe('Test Description');
            expect($dto->enabled)->toBeFalse();
            expect($dto->price)->toBeNull();
            expect($dto->metadata)->toBeNull();
        });

        test('uses data pipeline with cast primitive properties', function (): void {
            // Arrange
            $data = [
                'title' => 456,  // Will be cast to string
                'count' => '25',  // Will be cast to int
                'enabled' => 1,  // Will be cast to bool
                'price' => '149.99',  // Will be cast to float
                'metadata' => 'key:value',  // Will be cast to array
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->title)->toBe('456');
            expect($result->count)->toBe(25);
            expect($result->enabled)->toBeTrue();
            expect($result->price)->toBe(149.99);
            expect($result->metadata)->toBeArray();
        });

        test('replaces empty strings with null', function (): void {
            // Arrange
            $data = [
                'title' => 'Test Title',
                'count' => 5,
                'description' => '',  // Empty string should become null
                'enabled' => true,
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->title)->toBe('Test Title');
            expect($result->count)->toBe(5);
            expect($result->description)->toBeNull();
            expect($result->enabled)->toBeTrue();
        });

        test('replaces whitespace-only strings with null', function (): void {
            // Arrange
            $data = [
                'title' => 'Test Title',
                'count' => 5,
                'description' => '   ',  // Whitespace-only string should become null
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->title)->toBe('Test Title');
            expect($result->count)->toBe(5);
            expect($result->description)->toBeNull();
        });

        test('creates instance from object', function (): void {
            // Arrange
            $object = new stdClass();
            $object->title = 'Test Title';
            $object->count = 5;
            $object->description = 'Test Description';

            // Act
            $result = ConcreteDataTransferObject::from($object);

            // Assert
            expect($result->title)->toBe('Test Title');
            expect($result->count)->toBe(5);
            expect($result->description)->toBe('Test Description');
        });

        test('creates instance from json string', function (): void {
            // Arrange
            $json = json_encode([
                'title' => 'Test Title',
                'count' => 5,
                'description' => 'Test Description',
            ]);

            // Act
            $result = ConcreteDataTransferObject::from($json);

            // Assert
            expect($result->title)->toBe('Test Title');
            expect($result->count)->toBe(5);
            expect($result->description)->toBe('Test Description');
        });

        test('creates collection from array of data', function (): void {
            // Arrange
            $dataArray = [
                ['title' => 'First', 'count' => 1],
                ['title' => 'Second', 'count' => 2],
            ];

            // Act
            $result = ConcreteDataTransferObject::collect($dataArray);

            // Assert
            expect($result)->toHaveCount(2);
            expect($result[0]->title)->toBe('First');
            expect($result[1]->title)->toBe('Second');
        });

        test('inherits from Dto base class', function (): void {
            // Arrange
            $dto = ConcreteDataTransferObject::from([
                'title' => 'Test',
                'count' => 1,
            ]);

            // Act & Assert
            expect($dto)->toBeInstanceOf(Dto::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('throws exception when required property is missing', function (): void {
            // Arrange
            $data = ['count' => 5];  // Missing 'title'

            // Act & Assert
            expect(fn (): ConcreteDataTransferObject => ConcreteDataTransferObject::from($data))
                ->toThrow(Exception::class);
        });

        test('casts incompatible string to int using primitive casting', function (): void {
            // Arrange
            $data = [
                'title' => 'Test Title',
                'count' => 'not a number',  // Will be cast to int (0 in PHP)
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->count)->toBeInt();
        });
    });

    describe('Edge Cases', function (): void {
        test('handles unicode characters in string properties', function (): void {
            // Arrange
            $data = [
                'title' => 'Título en Español',
                'count' => 5,
                'description' => '日本語の説明',
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->title)->toBe('Título en Español');
            expect($result->description)->toBe('日本語の説明');
        });

        test('handles empty array for metadata property', function (): void {
            // Arrange
            $data = [
                'title' => 'Test',
                'count' => 5,
                'metadata' => [],
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->metadata)->toBeArray();
            expect($result->metadata)->toHaveCount(0);
        });

        test('handles zero values correctly', function (): void {
            // Arrange
            $data = [
                'title' => 'Test',
                'count' => 0,  // Zero should not be converted to null
                'price' => 0.0,
                'enabled' => false,
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->count)->toBe(0);
            expect($result->price)->toBe(0.0);
            expect($result->enabled)->toBeFalse();
        });

        test('handles negative numbers correctly', function (): void {
            // Arrange
            $data = [
                'title' => 'Test',
                'count' => -5,
                'price' => -99.99,
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->count)->toBe(-5);
            expect($result->price)->toBe(-99.99);
        });

        test('handles very large numbers', function (): void {
            // Arrange
            $data = [
                'title' => 'Test',
                'count' => \PHP_INT_MAX,
                'price' => \PHP_FLOAT_MAX,
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->count)->toBe(\PHP_INT_MAX);
            expect($result->price)->toBe(\PHP_FLOAT_MAX);
        });

        test('preserves string with only tabs and newlines as null', function (): void {
            // Arrange
            $data = [
                'title' => 'Test',
                'count' => 5,
                'description' => "\t\n\r",  // Whitespace characters should become null
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->description)->toBeNull();
        });

        test('handles nested array structures', function (): void {
            // Arrange
            $data = [
                'title' => 'Test',
                'count' => 5,
                'metadata' => ['level1' => ['level2' => ['level3' => 'deep value']]],
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->metadata)->toBeArray();
            expect($result->metadata['level1']['level2']['level3'])->toBe('deep value');
        });

        test('creates multiple instances independently', function (): void {
            // Arrange
            $data1 = ['title' => 'First', 'count' => 1];
            $data2 = ['title' => 'Second', 'count' => 2];

            // Act
            $result1 = ConcreteDataTransferObject::from($data1);
            $result2 = ConcreteDataTransferObject::from($data2);

            // Assert
            expect($result1->title)->toBe('First');
            expect($result2->title)->toBe('Second');
            expect($result1)->not->toBe($result2);
        });

        test('handles special characters in string values', function (): void {
            // Arrange
            $data = [
                'title' => 'Test "Title" with \'quotes\'',
                'count' => 5,
                'description' => '<script>alert("xss")</script>',
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->title)->toBe('Test "Title" with \'quotes\'');
            expect($result->description)->toBe('<script>alert("xss")</script>');
        });

        test('verifies pipeline configuration is inherited', function (): void {
            // Arrange & Act
            $pipeline = ConcreteDataTransferObject::pipeline();

            // Assert
            expect($pipeline)->toBeInstanceOf(DataPipeline::class);
        });

        test('handles boolean false vs null distinction', function (): void {
            // Arrange
            $data = [
                'title' => 'Test',
                'count' => 5,
                'enabled' => false,  // Explicit false, not null
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->enabled)->toBeFalse();
            expect($result->enabled)->not->toBeNull();
        });

        test('handles string zero vs null distinction', function (): void {
            // Arrange
            $data = [
                'title' => '0',  // String zero, not empty
                'count' => 5,
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->title)->toBe('0');
            expect($result->title)->not->toBeNull();
        });

        test('handles associative vs indexed arrays', function (): void {
            // Arrange
            $data = [
                'title' => 'Test',
                'count' => 5,
                'metadata' => ['a', 'b', 'c'],  // Indexed array
            ];

            // Act
            $result = ConcreteDataTransferObject::from($data);

            // Assert
            expect($result->metadata)->toBe(['a', 'b', 'c']);
            expect($result->metadata[0])->toBe('a');
        });
    });
});
