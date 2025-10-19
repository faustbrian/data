<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Spatie\LaravelData\DataPipeline;
use Tests\Fixtures\ConcreteData;

describe('AbstractData', function (): void {
    describe('Happy Paths', function (): void {
        test('creates instance with all required properties', function (): void {
            // Arrange
            $data = ['name' => 'John Doe', 'age' => 30];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->name)->toBe('John Doe');
            expect($result->age)->toBe(30);
            expect($result->email)->toBeNull();
            expect($result->active)->toBeFalse();
            expect($result->score)->toBeNull();
            expect($result->tags)->toBeNull();
        });

        test('creates instance with all properties including optional', function (): void {
            // Arrange
            $data = [
                'name' => 'Jane Smith',
                'age' => 25,
                'email' => 'jane@example.com',
                'active' => true,
                'score' => 95.5,
                'tags' => ['developer', 'designer'],
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->name)->toBe('Jane Smith');
            expect($result->age)->toBe(25);
            expect($result->email)->toBe('jane@example.com');
            expect($result->active)->toBeTrue();
            expect($result->score)->toBe(95.5);
            expect($result->tags)->toBe(['developer', 'designer']);
        });

        test('serializes to array correctly', function (): void {
            // Arrange
            $data = ConcreteData::from([
                'name' => 'John Doe',
                'age' => 30,
                'email' => 'john@example.com',
            ]);

            // Act
            $result = $data->toArray();

            // Assert
            expect($result)->toBeArray();
            expect($result['name'])->toBe('John Doe');
            expect($result['age'])->toBe(30);
            expect($result['email'])->toBe('john@example.com');
        });

        test('serializes to json correctly', function (): void {
            // Arrange
            $data = ConcreteData::from([
                'name' => 'John Doe',
                'age' => 30,
                'email' => 'john@example.com',
            ]);

            // Act
            $result = $data->toJson();

            // Assert
            expect($result)->toBeString();
            expect(json_decode($result, true))->toBe([
                'name' => 'John Doe',
                'age' => 30,
                'email' => 'john@example.com',
                'active' => false,
                'score' => null,
                'tags' => null,
            ]);
        });

        test('uses data pipeline with cast primitive properties', function (): void {
            // Arrange
            $data = [
                'name' => 123,  // Will be cast to string
                'age' => '42',  // Will be cast to int
                'active' => 1,  // Will be cast to bool
                'score' => '98.5',  // Will be cast to float
                'tags' => 'tag1,tag2',  // Will be cast to array
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->name)->toBe('123');
            expect($result->age)->toBe(42);
            expect($result->active)->toBeTrue();
            expect($result->score)->toBe(98.5);
            expect($result->tags)->toBeArray();
        });

        test('replaces empty strings with null', function (): void {
            // Arrange
            $data = [
                'name' => 'John Doe',
                'age' => 30,
                'email' => '',  // Empty string should become null
                'active' => true,
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->name)->toBe('John Doe');
            expect($result->age)->toBe(30);
            expect($result->email)->toBeNull();
            expect($result->active)->toBeTrue();
        });

        test('replaces whitespace-only strings with null', function (): void {
            // Arrange
            $data = [
                'name' => 'John Doe',
                'age' => 30,
                'email' => '   ',  // Whitespace-only string should become null
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->name)->toBe('John Doe');
            expect($result->age)->toBe(30);
            expect($result->email)->toBeNull();
        });

        test('creates instance from object', function (): void {
            // Arrange
            $object = new stdClass();
            $object->name = 'John Doe';
            $object->age = 30;
            $object->email = 'john@example.com';

            // Act
            $result = ConcreteData::from($object);

            // Assert
            expect($result->name)->toBe('John Doe');
            expect($result->age)->toBe(30);
            expect($result->email)->toBe('john@example.com');
        });

        test('creates instance from json string', function (): void {
            // Arrange
            $json = json_encode([
                'name' => 'John Doe',
                'age' => 30,
                'email' => 'john@example.com',
            ]);

            // Act
            $result = ConcreteData::from($json);

            // Assert
            expect($result->name)->toBe('John Doe');
            expect($result->age)->toBe(30);
            expect($result->email)->toBe('john@example.com');
        });

        test('creates collection from array of data', function (): void {
            // Arrange
            $dataArray = [
                ['name' => 'John Doe', 'age' => 30],
                ['name' => 'Jane Smith', 'age' => 25],
            ];

            // Act
            $result = ConcreteData::collect($dataArray);

            // Assert
            expect($result)->toHaveCount(2);
            expect($result[0]->name)->toBe('John Doe');
            expect($result[1]->name)->toBe('Jane Smith');
        });
    });

    describe('Sad Paths', function (): void {
        test('throws exception when required property is missing', function (): void {
            // Arrange
            $data = ['age' => 30];  // Missing 'name'

            // Act & Assert
            expect(fn (): ConcreteData => ConcreteData::from($data))
                ->toThrow(Exception::class);
        });

        test('casts incompatible string to int using primitive casting', function (): void {
            // Arrange
            $data = [
                'name' => 'John Doe',
                'age' => 'not a number',  // Will be cast to int (0 in PHP)
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->age)->toBeInt();
        });
    });

    describe('Edge Cases', function (): void {
        test('handles unicode characters in string properties', function (): void {
            // Arrange
            $data = [
                'name' => 'José García',
                'age' => 30,
                'email' => 'josé@example.com',
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->name)->toBe('José García');
            expect($result->email)->toBe('josé@example.com');
        });

        test('handles empty array for tags property', function (): void {
            // Arrange
            $data = [
                'name' => 'John Doe',
                'age' => 30,
                'tags' => [],
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->tags)->toBeArray();
            expect($result->tags)->toHaveCount(0);
        });

        test('handles zero values correctly', function (): void {
            // Arrange
            $data = [
                'name' => 'John Doe',
                'age' => 0,  // Zero should not be converted to null
                'score' => 0.0,
                'active' => false,
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->age)->toBe(0);
            expect($result->score)->toBe(0.0);
            expect($result->active)->toBeFalse();
        });

        test('handles negative numbers correctly', function (): void {
            // Arrange
            $data = [
                'name' => 'John Doe',
                'age' => -1,
                'score' => -99.9,
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->age)->toBe(-1);
            expect($result->score)->toBe(-99.9);
        });

        test('handles very large numbers', function (): void {
            // Arrange
            $data = [
                'name' => 'John Doe',
                'age' => \PHP_INT_MAX,
                'score' => \PHP_FLOAT_MAX,
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->age)->toBe(\PHP_INT_MAX);
            expect($result->score)->toBe(\PHP_FLOAT_MAX);
        });

        test('preserves string with only spaces after trim check', function (): void {
            // Arrange
            $data = [
                'name' => 'John Doe',
                'age' => 30,
                'email' => "\t\n\r",  // Whitespace characters should become null
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->email)->toBeNull();
        });

        test('handles nested array structures', function (): void {
            // Arrange
            $data = [
                'name' => 'John Doe',
                'age' => 30,
                'tags' => ['primary' => ['developer', 'designer'], 'secondary' => ['manager']],
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->tags)->toBeArray();
            expect($result->tags['primary'])->toBe(['developer', 'designer']);
            expect($result->tags['secondary'])->toBe(['manager']);
        });

        test('creates multiple instances independently', function (): void {
            // Arrange
            $data1 = ['name' => 'John Doe', 'age' => 30];
            $data2 = ['name' => 'Jane Smith', 'age' => 25];

            // Act
            $result1 = ConcreteData::from($data1);
            $result2 = ConcreteData::from($data2);

            // Assert
            expect($result1->name)->toBe('John Doe');
            expect($result2->name)->toBe('Jane Smith');
            expect($result1)->not->toBe($result2);
        });

        test('handles special characters in string values', function (): void {
            // Arrange
            $data = [
                'name' => 'John "The Boss" Doe',
                'age' => 30,
                'email' => 'john+test@example.com',
            ];

            // Act
            $result = ConcreteData::from($data);

            // Assert
            expect($result->name)->toBe('John "The Boss" Doe');
            expect($result->email)->toBe('john+test@example.com');
        });

        test('verifies pipeline configuration is inherited', function (): void {
            // Arrange & Act
            $pipeline = ConcreteData::pipeline();

            // Assert
            expect($pipeline)->toBeInstanceOf(DataPipeline::class);
        });
    });
});
