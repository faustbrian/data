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
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;

use function is_string;
use function preg_replace;

/**
 * Normalizes empty and whitespace-only string values to null during data object creation.
 *
 * This pipe detects strings that appear empty after removing all whitespace, including
 * standard whitespace and Unicode zero-width characters (zero-width space, zero-width
 * non-joiner, zero-width joiner, zero-width no-break space). This prevents "empty but
 * not null" string values from polluting data objects and ensures consistent null handling.
 *
 * Useful for APIs that return empty strings instead of null values, or for form inputs
 * where whitespace-only submissions should be treated as empty fields.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final class ReplaceEmptyStringsWithNullDataPipe implements DataPipe
{
    /**
     * Processes string properties and replaces empty or whitespace-only values with null.
     *
     * Iterates through all properties and applies aggressive whitespace removal using regex
     * to detect truly empty strings, including those containing only Unicode zero-width
     * characters. Non-string values are left unchanged.
     *
     * @param  mixed                $payload         The raw input data being transformed into the data object
     * @param  DataClass            $class           Metadata about the target data class structure and properties
     * @param  array<string, mixed> $properties      Property name-value pairs to be processed
     * @param  CreationContext      $creationContext Context information about the data creation operation
     * @return array<string, mixed> Properties with empty strings normalized to null
     */
    #[Override()]
    public function handle(mixed $payload, DataClass $class, array $properties, CreationContext $creationContext): array
    {
        foreach ($properties as $name => $value) {
            $dataProperty = $class->properties->first(static fn (DataProperty $dataProperty): bool => $dataProperty->name === $name);

            if ($dataProperty === null) {
                continue;
            }

            if (!is_string($value)) {
                continue;
            }

            // Remove all whitespace including zero-width spaces and unicode whitespace
            $trimmed = preg_replace('/[\s\x{200B}\x{200C}\x{200D}\x{FEFF}]/u', '', $value);

            if ($trimmed !== '') {
                continue;
            }

            $properties[$name] = null;
        }

        return $properties;
    }
}
