<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Core\AbstractData;
use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Tests\Fixtures\ConcreteValidatedData;

describe('AbstractValidatedData', function (): void {
    describe('Happy Paths', function (): void {
        test('creates instance with valid data passing all validations', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 25,
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->username)->toBe('johndoe');
            expect($result->email)->toBe('john@example.com');
            expect($result->age)->toBe(25);
            expect($result->bio)->toBeNull();
        });

        test('creates instance with optional properties', function (): void {
            // Arrange
            $data = [
                'username' => 'janedoe',
                'email' => 'jane@example.com',
                'age' => 30,
                'bio' => 'Software developer',
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->username)->toBe('janedoe');
            expect($result->email)->toBe('jane@example.com');
            expect($result->age)->toBe(30);
            expect($result->bio)->toBe('Software developer');
        });

        test('validates minimum age constraint', function (): void {
            // Arrange
            $data = [
                'username' => 'younguser',
                'email' => 'young@example.com',
                'age' => 1,  // Minimum allowed
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->age)->toBe(1);
        });

        test('validates maximum age constraint', function (): void {
            // Arrange
            $data = [
                'username' => 'olduser',
                'email' => 'old@example.com',
                'age' => 120,  // Maximum allowed
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->age)->toBe(120);
        });

        test('serializes validated data to array', function (): void {
            // Arrange
            $validatedData = ConcreteValidatedData::from([
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 25,
            ]);

            // Act
            $result = $validatedData->toArray();

            // Assert
            expect($result)->toBeArray();
            expect($result['username'])->toBe('johndoe');
            expect($result['email'])->toBe('john@example.com');
            expect($result['age'])->toBe(25);
        });

        test('serializes validated data to json', function (): void {
            // Arrange
            $validatedData = ConcreteValidatedData::from([
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 25,
            ]);

            // Act
            $result = $validatedData->toJson();

            // Assert
            expect($result)->toBeString();
            $decoded = json_decode($result, true);
            expect($decoded['username'])->toBe('johndoe');
            expect($decoded['email'])->toBe('john@example.com');
            expect($decoded['age'])->toBe(25);
        });

        test('validates with data pipeline transformations', function (): void {
            // Arrange
            $data = [
                'username' => 123,  // Will be cast to string
                'email' => 'test@example.com',
                'age' => '35',  // Will be cast to int
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->username)->toBe('123');
            expect($result->age)->toBe(35);
        });

        test('replaces empty strings with null during validation', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 25,
                'bio' => '',  // Empty string should become null
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->bio)->toBeNull();
        });

        test('creates collection of validated data', function (): void {
            // Arrange
            $dataArray = [
                ['username' => 'user1', 'email' => 'user1@example.com', 'age' => 25],
                ['username' => 'user2', 'email' => 'user2@example.com', 'age' => 30],
            ];

            // Act
            $result = ConcreteValidatedData::collect($dataArray);

            // Assert
            expect($result)->toHaveCount(2);
            expect($result[0]->username)->toBe('user1');
            expect($result[1]->username)->toBe('user2');
        });

        test('inherits from AbstractData base class', function (): void {
            // Arrange
            $data = ConcreteValidatedData::from([
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 25,
            ]);

            // Act & Assert
            expect($data)->toBeInstanceOf(AbstractData::class);
        });

        test('uses validation strategy always', function (): void {
            // Arrange & Act
            $factory = ConcreteValidatedData::factory();

            // Assert
            expect($factory)->toBeInstanceOf(CreationContextFactory::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('fails validation when required field is missing', function (): void {
            // Arrange
            $data = [
                'email' => 'john@example.com',
                'age' => 25,
                // Missing 'username'
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedData => ConcreteValidatedData::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when email format is invalid', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'not-an-email',  // Invalid email
                'age' => 25,
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedData => ConcreteValidatedData::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when age is below minimum', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 0,  // Below minimum of 1
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedData => ConcreteValidatedData::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when age exceeds maximum', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 121,  // Above maximum of 120
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedData => ConcreteValidatedData::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when age is cast to zero from non-numeric string', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 'twenty-five',  // Will be cast to 0, which fails Min(1)
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedData => ConcreteValidatedData::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when multiple fields are invalid', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'invalid-email',  // Invalid email
                'age' => 150,  // Above maximum
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedData => ConcreteValidatedData::from($data))
                ->toThrow(Exception::class);
        });

        test('fails validation when all required fields are missing', function (): void {
            // Arrange
            $data = [];

            // Act & Assert
            expect(fn (): ConcreteValidatedData => ConcreteValidatedData::from($data))
                ->toThrow(Exception::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('handles unicode characters in validated string fields', function (): void {
            // Arrange
            $data = [
                'username' => 'josé_garcía',
                'email' => 'josé@example.com',
                'age' => 30,
                'bio' => '日本語のプロフィール',
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->username)->toBe('josé_garcía');
            expect($result->email)->toBe('josé@example.com');
            expect($result->bio)->toBe('日本語のプロフィール');
        });

        test('handles email with special characters', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'john+test@example.co.uk',
                'age' => 25,
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->email)->toBe('john+test@example.co.uk');
        });

        test('handles whitespace in optional bio field', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 25,
                'bio' => '   ',  // Whitespace should become null
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->bio)->toBeNull();
        });

        test('validates negative age correctly fails', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => -5,  // Negative age should fail
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedData => ConcreteValidatedData::from($data))
                ->toThrow(Exception::class);
        });

        test('handles very long username', function (): void {
            // Arrange
            $longUsername = str_repeat('a', 255);
            $data = [
                'username' => $longUsername,
                'email' => 'john@example.com',
                'age' => 25,
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->username)->toBe($longUsername);
        });

        test('handles very long bio', function (): void {
            // Arrange
            $longBio = str_repeat('This is a long bio. ', 100);
            $data = [
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 25,
                'bio' => $longBio,
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->bio)->toBe($longBio);
        });

        test('validates email with subdomain', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'john@mail.example.com',
                'age' => 25,
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->email)->toBe('john@mail.example.com');
        });

        test('creates multiple validated instances independently', function (): void {
            // Arrange
            $data1 = ['username' => 'user1', 'email' => 'user1@example.com', 'age' => 25];
            $data2 = ['username' => 'user2', 'email' => 'user2@example.com', 'age' => 30];

            // Act
            $result1 = ConcreteValidatedData::from($data1);
            $result2 = ConcreteValidatedData::from($data2);

            // Assert
            expect($result1->username)->toBe('user1');
            expect($result2->username)->toBe('user2');
            expect($result1)->not->toBe($result2);
        });

        test('validates with json string input', function (): void {
            // Arrange
            $json = json_encode([
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 25,
            ]);

            // Act
            $result = ConcreteValidatedData::from($json);

            // Assert
            expect($result->username)->toBe('johndoe');
            expect($result->email)->toBe('john@example.com');
            expect($result->age)->toBe(25);
        });

        test('validates with object input', function (): void {
            // Arrange
            $object = new stdClass();
            $object->username = 'johndoe';
            $object->email = 'john@example.com';
            $object->age = 25;

            // Act
            $result = ConcreteValidatedData::from($object);

            // Assert
            expect($result->username)->toBe('johndoe');
            expect($result->email)->toBe('john@example.com');
            expect($result->age)->toBe(25);
        });

        test('validates boundary age values', function (): void {
            // Arrange
            $data1 = ['username' => 'user1', 'email' => 'user1@example.com', 'age' => 1];
            $data2 = ['username' => 'user2', 'email' => 'user2@example.com', 'age' => 120];

            // Act
            $result1 = ConcreteValidatedData::from($data1);
            $result2 = ConcreteValidatedData::from($data2);

            // Assert
            expect($result1->age)->toBe(1);
            expect($result2->age)->toBe(120);
        });

        test('verifies pipeline configuration is inherited from AbstractData', function (): void {
            // Arrange & Act
            $pipeline = ConcreteValidatedData::pipeline();

            // Assert
            expect($pipeline)->toBeInstanceOf(DataPipeline::class);
        });

        test('handles special characters in bio', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'age' => 25,
                'bio' => '<p>HTML & special chars: @#$%^&*()</p>',
            ];

            // Act
            $result = ConcreteValidatedData::from($data);

            // Assert
            expect($result->bio)->toBe('<p>HTML & special chars: @#$%^&*()</p>');
        });

        test('fails validation with whitespace-padded email', function (): void {
            // Arrange
            $data = [
                'username' => 'johndoe',
                'email' => '  john@example.com  ',  // Whitespace around email fails validation
                'age' => 25,
            ];

            // Act & Assert
            expect(fn (): ConcreteValidatedData => ConcreteValidatedData::from($data))
                ->toThrow(Exception::class);
        });
    });
});
