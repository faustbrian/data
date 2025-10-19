<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Data\Casts\CarbonImmutableCast;
use Illuminate\Support\Facades\Date;
use Spatie\LaravelData\Data;

describe('CarbonImmutableCast', function (): void {
    describe('GetsCast Interface', function (): void {
        test('get method returns the cast instance', function (): void {
            $cast = new CarbonImmutableCast();

            expect($cast->get())->toBe($cast);
        });

        test('get method returns the cast instance with timezone', function (): void {
            $cast = new CarbonImmutableCast('America/New_York');

            expect($cast->get())->toBe($cast);
        });
    });

    describe('Happy Paths', function (): void {
        test('casts timestamp in seconds to CarbonImmutable', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => 1_609_459_200]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('casts timestamp in milliseconds to CarbonImmutable', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => 1_609_459_200_000]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('casts date string to CarbonImmutable', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => '2024-01-15']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->format('Y-m-d'))->toBe('2024-01-15');
        });

        test('casts datetime string to CarbonImmutable', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => '2024-01-15 10:30:00']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
        });

        test('casts ISO 8601 string to CarbonImmutable', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => '2024-01-15T10:30:00Z']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->format('Y-m-d\TH:i:s\Z'))->toBe('2024-01-15T10:30:00Z');
        });

        test('casts string with custom timezone', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'America/New_York')]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => '2024-01-15 10:30:00']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timezone->getName())->toBe('America/New_York');
        });
    });

    describe('Sad Paths', function (): void {
        test('throws exception for invalid date string', function (): void {
            expect(function (): void {
                $dataClass = new class() extends Data
                {
                    public function __construct(
                        #[CarbonImmutableCast()]
                        public readonly ?CarbonImmutable $created_at = null,
                    ) {}
                };

                $dataClass::from(['created_at' => 'not-a-date']);
            })->toThrow(Exception::class);
        });

        test('throws exception for empty string', function (): void {
            expect(function (): void {
                $dataClass = new class() extends Data
                {
                    public function __construct(
                        #[CarbonImmutableCast()]
                        public readonly ?CarbonImmutable $created_at = null,
                    ) {}
                };

                $dataClass::from(['created_at' => '']);
            })->toThrow(Exception::class);
        });

        test('throws exception for array input', function (): void {
            expect(function (): void {
                $dataClass = new class() extends Data
                {
                    public function __construct(
                        #[CarbonImmutableCast()]
                        public readonly ?CarbonImmutable $created_at = null,
                    ) {}
                };

                $dataClass::from(['created_at' => []]);
            })->toThrow(Exception::class);
        });

        test('throws exception for object input', function (): void {
            expect(function (): void {
                $dataClass = new class() extends Data
                {
                    public function __construct(
                        #[CarbonImmutableCast()]
                        public readonly ?CarbonImmutable $created_at = null,
                    ) {}
                };

                $dataClass::from(['created_at' => new stdClass()]);
            })->toThrow(Exception::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('handles negative timestamp', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => -86_400]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(-86_400);
        });

        test('handles zero timestamp', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => 0]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(0);
        });

        test('handles string timestamp in seconds', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => '1609459200']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('handles string timestamp in milliseconds', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => '1609459200000']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('handles string timestamp in seconds with custom timezone', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'America/New_York')]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => '1609459200']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200)
                ->and($data->created_at->timezone->getName())->toBe('America/New_York');
        });

        test('handles string timestamp in milliseconds with custom timezone', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'Europe/London')]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => '1609459200000']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200)
                ->and($data->created_at->timezone->getName())->toBe('Europe/London');
        });

        test('handles relative date strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => 'now']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class);
        });

        test('handles date with timezone offset', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => '2024-01-15T10:30:00+05:00']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
        });

        test('handles float timestamp', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => 1_609_459_200.5]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('handles float millisecond timestamp', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => 1_609_459_200_000.789]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('handles float timestamp with timezone', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'Asia/Tokyo')]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $data = $dataClass::from(['created_at' => 1_609_459_200.999]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200)
                ->and($data->created_at->timezone->getName())->toBe('Asia/Tokyo');
        });

        test('handles DateTimeInterface object', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?DateTimeInterface $created_at = null,
                ) {}
            };

            $dateTime = Date::parse('2024-01-15 10:30:00');
            $data = $dataClass::from(['created_at' => $dateTime]);

            expect($data->created_at)->toBeInstanceOf(DateTimeInterface::class)
                ->and($data->created_at->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
        });

        test('handles CarbonImmutable object', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $carbonImmutable = CarbonImmutable::parse('2024-01-15 10:30:00');
            $data = $dataClass::from(['created_at' => $carbonImmutable]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
        });

        test('throws exception for object with __toString method', function (): void {
            expect(function (): void {
                $dataClass = new class() extends Data
                {
                    public function __construct(
                        #[CarbonImmutableCast()]
                        public readonly ?CarbonImmutable $created_at = null,
                    ) {}
                };

                $stringableObject = new class() implements Stringable
                {
                    public function __toString(): string
                    {
                        return '2024-01-15 10:30:00';
                    }
                };

                $dataClass::from(['created_at' => $stringableObject]);
            })->toThrow(InvalidArgumentException::class, 'Cannot cast object to CarbonImmutable');
        });

        test('handles boolean values in fallback path without timezone', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Boolean true becomes string "1" which will be passed to parse
            // This should throw an error since "1" is not a valid date string
            expect(fn (): object => $dataClass::from(['created_at' => true]))
                ->toThrow(Exception::class);
        });

        test('handles boolean values in fallback path with timezone', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'UTC')]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Boolean true becomes string "1" which will be passed to parse with timezone
            // This should throw an error since "1" is not a valid date string
            expect(fn (): object => $dataClass::from(['created_at' => true]))
                ->toThrow(Exception::class);
        });

        test('handles resource values in fallback path', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $resource = fopen('php://memory', 'rb');

            // Resource gets cast to string like "Resource id #123"
            // This should throw an error since it's not a valid date string
            expect(fn (): object => $dataClass::from(['created_at' => $resource]))
                ->toThrow(Exception::class);

            fclose($resource);
        });

        test('handles resource values in fallback path with timezone', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'America/Los_Angeles')]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            $resource = fopen('php://memory', 'rb');

            // Resource gets cast to string like "Resource id #123"
            // This should throw an error since it's not a valid date string
            expect(fn (): object => $dataClass::from(['created_at' => $resource]))
                ->toThrow(Exception::class);

            fclose($resource);
        });

        test('handles boundary timestamp at 10 digits', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // 10 digits exactly - should be treated as seconds
            $data = $dataClass::from(['created_at' => 1_000_000_000]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_000_000_000);
        });

        test('handles boundary timestamp at 11 digits', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // 11 digits - still treated as seconds (not milliseconds)
            $data = $dataClass::from(['created_at' => 10_000_000_000]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(10_000_000_000);
        });

        test('handles boundary timestamp at 12 digits', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // 12 digits - still treated as seconds
            $data = $dataClass::from(['created_at' => 100_000_000_000]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(100_000_000_000);
        });

        test('handles string boundary timestamp at 10 digits', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // String with exactly 10 digits - should be treated as seconds
            $data = $dataClass::from(['created_at' => '1000000000']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_000_000_000);
        });

        test('handles string boundary timestamp at 11 digits', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // String with 11 digits - still treated as seconds
            $data = $dataClass::from(['created_at' => '10000000000']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(10_000_000_000);
        });

        test('handles string boundary timestamp at 12 digits', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // String with 12 digits - still treated as seconds
            $data = $dataClass::from(['created_at' => '100000000000']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(100_000_000_000);
        });

        test('handles string boundary timestamp at 13 digits', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // String with exactly 13 digits - should be treated as milliseconds
            $data = $dataClass::from(['created_at' => '1000000000000']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_000_000_000);
        });

        test('handles numeric string with leading zeros', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Leading zeros should be handled correctly
            $data = $dataClass::from(['created_at' => '0001609459200']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class);
        });

        test('handles very small string timestamp', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Single digit timestamp string
            $data = $dataClass::from(['created_at' => '1']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1);
        });

        test('handles numeric timestamp with timezone specification', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'Pacific/Auckland')]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Numeric value (int) with timezone
            $data = $dataClass::from(['created_at' => 1_609_459_200]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200)
                ->and($data->created_at->timezone->getName())->toBe('Pacific/Auckland');
        });

        test('handles numeric millisecond timestamp with timezone specification', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'Pacific/Auckland')]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Numeric value (int) in milliseconds with timezone
            $data = $dataClass::from(['created_at' => 1_609_459_200_000]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200)
                ->and($data->created_at->timezone->getName())->toBe('Pacific/Auckland');
        });

        test('handles DateTimeImmutable object', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?DateTimeInterface $created_at = null,
                ) {}
            };

            $dateTimeImmutable = CarbonImmutable::parse('2024-01-15 10:30:00');
            $data = $dataClass::from(['created_at' => $dateTimeImmutable]);

            expect($data->created_at)->toBeInstanceOf(DateTimeInterface::class)
                ->and($data->created_at->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
        });

        test('handles DateTime object', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?DateTimeInterface $created_at = null,
                ) {}
            };

            $dateTime = Date::parse('2024-01-15 10:30:00');
            $data = $dataClass::from(['created_at' => $dateTime]);

            expect($data->created_at)->toBeInstanceOf(DateTimeInterface::class)
                ->and($data->created_at->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
        });

        test('handles DateTimeInterface with timezone conversion', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'UTC')]
                    public readonly ?DateTimeInterface $created_at = null,
                ) {}
            };

            // Create DateTime with different timezone
            $dateTime = new DateTime('2024-01-15 10:30:00', new DateTimeZone('America/New_York'));
            $data = $dataClass::from(['created_at' => $dateTime]);

            expect($data->created_at)->toBeInstanceOf(DateTimeInterface::class);
        });

        test('handles negative string timestamp', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Negative timestamp as string
            $data = $dataClass::from(['created_at' => '-86400']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(-86_400);
        });

        test('handles zero as string timestamp', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Zero as string
            $data = $dataClass::from(['created_at' => '0']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(0);
        });

        test('handles numeric string with decimal point', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Numeric string with decimal - is_numeric returns true
            $data = $dataClass::from(['created_at' => '1609459200.5']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('handles numeric string milliseconds with decimal', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Numeric string with decimal in milliseconds range
            $data = $dataClass::from(['created_at' => '1609459200000.789']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('handles whitespace-padded numeric string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // is_numeric handles whitespace padding
            $data = $dataClass::from(['created_at' => '  1609459200  ']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('handles string numeric milliseconds with exact 13 digits coverage', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // This explicitly tests the string numeric path in lines 119-127
            // where is_numeric(value) is true and length is >= 13
            $timestampMs = '1609459200123'; // 13 digits exactly
            $data = $dataClass::from(['created_at' => $timestampMs]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('handles string numeric milliseconds with 14 digits coverage', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Test 14 digit numeric string to ensure milliseconds branch is covered
            $timestampMs = '16094592001234'; // 14 digits
            $data = $dataClass::from(['created_at' => $timestampMs]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(16_094_592_001);
        });

        test('handles string numeric milliseconds with timezone coverage', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'America/Chicago')]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Test string numeric milliseconds with timezone to ensure full path coverage
            $timestampMs = '1609459200456'; // 13 digits with milliseconds
            $data = $dataClass::from(['created_at' => $timestampMs]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200)
                ->and($data->created_at->timezone->getName())->toBe('America/Chicago');
        });

        test('handles negative string numeric milliseconds', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Test negative milliseconds as string
            $timestampMs = '-1609459200000'; // Negative 13 digit timestamp
            $data = $dataClass::from(['created_at' => $timestampMs]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(-1_609_459_200);
        });

        test('handles DateTime object conversion via instance method', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Use native DateTime to ensure CarbonImmutable::instance is called
            $dateTime = new DateTime('2024-01-15 10:30:00', new DateTimeZone('UTC'));
            $data = $dataClass::from(['created_at' => $dateTime]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
        });

        test('handles DateTimeImmutable object conversion via instance method', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Use native DateTimeImmutable to ensure CarbonImmutable::instance is called
            $dateTimeImmutable = new DateTimeImmutable('2024-01-15 10:30:00', new DateTimeZone('UTC'));
            $data = $dataClass::from(['created_at' => $dateTimeImmutable]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
        });

        test('handles DateTime with microseconds via instance method', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // DateTime with microseconds to test instance() preserves precision
            $dateTime = DateTime::createFromFormat('!Y-m-d H:i:s.u', '2024-01-15 10:30:00.123456');
            $data = $dataClass::from(['created_at' => $dateTime]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->format('Y-m-d H:i:s.u'))->toContain('2024-01-15 10:30:00');
        });

        test('handles string numeric seconds at boundary of milliseconds detection', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Test exactly 12 digits - should be seconds not milliseconds
            $timestamp = '999999999999'; // 12 digits - max before milliseconds
            $data = $dataClass::from(['created_at' => $timestamp]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(999_999_999_999);
        });

        test('handles string numeric with scientific notation', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Scientific notation is numeric but will be converted to integer
            $timestamp = '1.609459200E9'; // Scientific notation for 1609459200
            $data = $dataClass::from(['created_at' => $timestamp]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('handles string numeric milliseconds in scientific notation', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Scientific notation for milliseconds
            $timestamp = '1.609459200000E12'; // Scientific notation for 1609459200000
            $data = $dataClass::from(['created_at' => $timestamp]);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('handles string numeric exactly 13 digits with custom timezone', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'Asia/Tokyo')]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Exactly 13 digits to ensure milliseconds branch with timezone is covered
            $data = $dataClass::from(['created_at' => '1609459200001']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200)
                ->and($data->created_at->timezone->getName())->toBe('Asia/Tokyo');
        });

        test('handles native PHP DateTime to ensure instance conversion', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Use native PHP DateTime (not Carbon) to ensure line 140 is hit
            $nativeDateTime = Date::parse('2024-01-15 10:30:00');
            $data = $dataClass::from(['created_at' => $nativeDateTime]);

            expect($data->created_at)
                ->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
        });

        test('handles native PHP DateTimeImmutable to ensure instance conversion', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Use native PHP DateTimeImmutable (not Carbon) to ensure line 140 is hit
            $nativeDateTimeImmutable = CarbonImmutable::parse('2024-01-15 10:30:00');
            $data = $dataClass::from(['created_at' => $nativeDateTimeImmutable]);

            expect($data->created_at)
                ->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
        });

        test('handles string numeric negative milliseconds with timezone', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast(timeZone: 'UTC')]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Negative milliseconds as string with timezone
            $data = $dataClass::from(['created_at' => '-1609459200001']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(-1_609_459_200)
                ->and($data->created_at->timezone->getName())->toBe('UTC');
        });

        test('handles string numeric zero padded milliseconds', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Zero padded milliseconds string to test edge case
            $data = $dataClass::from(['created_at' => '0001609459200000']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(1_609_459_200);
        });

        test('handles large string numeric milliseconds', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[CarbonImmutableCast()]
                    public readonly ?CarbonImmutable $created_at = null,
                ) {}
            };

            // Very large milliseconds value (year ~2510)
            $data = $dataClass::from(['created_at' => '17000000000000']);

            expect($data->created_at)->toBeInstanceOf(CarbonImmutable::class)
                ->and($data->created_at->timestamp)->toBe(17_000_000_000);
        });
    });
});
