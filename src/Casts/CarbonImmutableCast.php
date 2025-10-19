<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Casts;

use Attribute;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Override;
use Spatie\LaravelData\Attributes\GetsCast;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

use function is_array;
use function is_numeric;
use function is_object;
use function is_string;
use function mb_strlen;
use function throw_if;

/**
 * Converts various date/time representations to CarbonImmutable instances.
 *
 * Provides flexible date/time casting that handles timestamps (seconds or milliseconds),
 * date strings, and DateTimeInterface objects. Automatically detects timestamp precision
 * based on digit count and supports optional timezone specification for all conversions.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute()]
final readonly class CarbonImmutableCast implements Cast, GetsCast
{
    /**
     * Create a new CarbonImmutable cast instance.
     *
     * @param null|string $timeZone Optional timezone string (e.g., "UTC", "America/New_York")
     *                              to apply when parsing date strings or creating instances
     *                              from timestamps. When null, uses the system default timezone
     *                              or the timezone encoded in the date string itself.
     */
    public function __construct(
        private ?string $timeZone = null,
    ) {}

    /**
     * Returns the cast instance for the Spatie Laravel Data package.
     *
     * @return Cast The current cast instance
     */
    public function get(): Cast
    {
        return $this;
    }

    /**
     * Casts a value to a CarbonImmutable date/time instance.
     *
     * Supports multiple input formats:
     * - Numeric timestamps: Automatically detects seconds (10 digits or fewer) vs
     *   milliseconds (13+ digits) based on value length
     * - Date strings: Parses standard date/time formats like ISO 8601, RFC 3339
     * - DateTimeInterface objects: Returns as-is or converts to CarbonImmutable
     *
     * The cast automatically handles timezone conversion when the $timeZone parameter
     * is set during construction.
     *
     * @param DataProperty         $property   The property being cast
     * @param mixed                $value      The value to cast (timestamp, date string, or DateTimeInterface)
     * @param array<string, mixed> $properties All properties in the data object
     * @param CreationContext      $context    The creation context for the data object
     *
     * @throws InvalidArgumentException When arrays, invalid objects, or empty strings are provided
     *
     * @return DateTimeInterface The CarbonImmutable instance representing the date/time
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): DateTimeInterface
    {
        // Validate input type - reject arrays and non-DateTimeInterface objects
        throw_if(is_array($value), InvalidArgumentException::class, 'Cannot cast array to CarbonImmutable');

        throw_if(is_object($value) && !($value instanceof DateTimeInterface), InvalidArgumentException::class, 'Cannot cast object to CarbonImmutable');

        // Validate empty strings
        throw_if(is_string($value) && $value === '', InvalidArgumentException::class, 'Cannot cast empty string to CarbonImmutable');

        // Handle numeric values (int or float timestamps)
        if (is_numeric($value)) {
            // Convert to string to check length
            $timestamp = (string) $value;

            // Remove any decimal point for length check
            $integerPart = (int) $value;
            $integerPartString = (string) $integerPart;

            // Detect timestamp format by length
            // 13+ digits = milliseconds, 10 or fewer = seconds
            if (mb_strlen($integerPartString) >= 13) {
                return CarbonImmutable::createFromTimestampMs($integerPart, $this->timeZone);
            }

            return CarbonImmutable::createFromTimestamp($integerPart, $this->timeZone);
        }

        // Handle string values
        if (is_string($value)) {
            // Check if string is numeric (timestamp as string)
            if (is_numeric($value)) {
                $integerPart = (int) $value;
                $integerPartString = (string) $integerPart;

                // 13+ digits = milliseconds, 10 or fewer = seconds
                if (mb_strlen($integerPartString) >= 13) {
                    return CarbonImmutable::createFromTimestampMs($integerPart, $this->timeZone);
                }

                return CarbonImmutable::createFromTimestamp($integerPart, $this->timeZone);
            }

            // Otherwise parse as date string with timezone if provided
            if (is_string($this->timeZone)) {
                return CarbonImmutable::parse(time: $value, timezone: $this->timeZone);
            }

            return CarbonImmutable::parse($value);
        }

        // For DateTimeInterface objects, convert to CarbonImmutable
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        // For any other type, attempt to cast to string and parse
        // Note: This handles edge cases like booleans, resources, etc. by converting to string
        if (is_string($this->timeZone)) {
            return CarbonImmutable::parse(time: (string) $value, timezone: $this->timeZone); // @phpstan-ignore cast.string
        }

        return CarbonImmutable::parse((string) $value); // @phpstan-ignore cast.string
    }
}
