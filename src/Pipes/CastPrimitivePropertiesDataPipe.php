<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Pipes;

use Override;
use Spatie\LaravelData\DataPipes\DataPipe;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;

use function is_array;

/**
 * Automatically casts property values to their declared primitive types during data object creation.
 *
 * This pipe enforces type consistency by casting values to match their property type declarations,
 * respecting union type order (the first matching primitive type is selected). This ensures that
 * loosely-typed API inputs are normalized to the expected types without requiring manual casting.
 *
 * Supports casting to: array, bool, float, int, string. Skips null values, Optional instances,
 * and Lazy instances to preserve their special handling semantics.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final class CastPrimitivePropertiesDataPipe implements DataPipe
{
    /**
     * Processes property values and casts them to their declared primitive types.
     *
     * Iterates through all properties and casts each value to match its type declaration,
     * prioritizing the first matching primitive type in union type definitions. Arrays
     * are cast to empty strings when converted to strings to avoid PHP conversion errors.
     *
     * @param  mixed                $payload         The raw input data being transformed into the data object
     * @param  DataClass            $class           Metadata about the target data class structure and properties
     * @param  array<string, mixed> $properties      Property name-value pairs to be processed and cast
     * @param  CreationContext      $creationContext Context information about the data creation operation
     * @return array<string, mixed> Properties with values cast to their declared primitive types
     */
    #[Override()]
    public function handle(mixed $payload, DataClass $class, array $properties, CreationContext $creationContext): array
    {
        foreach ($properties as $name => $value) {
            $dataProperty = $class->properties->first(static fn (DataProperty $dataProperty): bool => $dataProperty->name === $name);

            if ($dataProperty === null) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            if ($value instanceof Optional) {
                continue;
            }

            if ($value instanceof Lazy) {
                continue;
            }

            // Get the first matching type from the property's type definition
            // This respects union type order (e.g., string|int will prefer string)
            $targetType = self::getFirstMatchingType($dataProperty);

            if ($targetType === null) {
                continue;
            }

            $properties[$name] = match ($targetType) {
                'array' => (array) $value,
                'bool' => (bool) $value,
                'float' => (float) $value, // @phpstan-ignore cast.double
                'int' => (int) $value, // @phpstan-ignore cast.int
                'string' => self::castToString($value),
                default => $value,
            };
        }

        return $properties;
    }

    /**
     * Identifies the first primitive type that matches the property's type declaration.
     *
     * Checks primitive types in priority order (string, int, float, bool, array) and returns
     * the first match. This ensures consistent casting behavior for union types by respecting
     * a predictable type hierarchy.
     *
     * @param  DataProperty $dataProperty Property metadata containing type information
     * @return null|string  The first matching primitive type name, or null if no primitive types match
     */
    private static function getFirstMatchingType(DataProperty $dataProperty): ?string
    {
        $types = ['string', 'int', 'float', 'bool', 'array'];

        foreach ($types as $type) {
            if ($dataProperty->type->acceptsType($type)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Safely casts a value to string, handling arrays to prevent PHP conversion errors.
     *
     * Arrays cannot be directly cast to strings in PHP without triggering a warning.
     * This method returns an empty string for arrays and casts all other types normally.
     *
     * @param  mixed  $value The value to cast to string
     * @return string The string representation, or empty string for arrays
     */
    private static function castToString(mixed $value): string
    {
        // Handle arrays specially to avoid "Array to string conversion" error
        if (is_array($value)) {
            return '';
        }

        return (string) $value; // @phpstan-ignore cast.string
    }
}
