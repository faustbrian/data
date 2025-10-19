<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Pipes\ReplaceEmptyStringsWithNullDataPipe;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataPipeline;

describe('ReplaceEmptyStringsWithNullDataPipe', function (): void {
    describe('Happy Paths', function (): void {
        test('replaces empty string with null', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['name' => '']);

            expect($data->name)->toBeNull();
        });

        test('replaces whitespace-only string with null', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $description = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['description' => '   ']);

            expect($data->description)->toBeNull();
        });

        test('replaces tab-only string with null', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $value = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['value' => "\t\t"]);

            expect($data->value)->toBeNull();
        });

        test('replaces newline-only string with null', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $text = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['text' => "\n\n"]);

            expect($data->text)->toBeNull();
        });

        test('replaces mixed whitespace string with null', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $content = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['content' => " \t\n\r "]);

            expect($data->content)->toBeNull();
        });

        test('replaces multiple empty string properties', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from([
                'name' => '',
                'email' => '   ',
                'phone' => "\t",
            ]);

            expect($data->name)->toBeNull();
            expect($data->email)->toBeNull();
            expect($data->phone)->toBeNull();
        });

        test('processes mixed empty and non-empty strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                    public readonly ?string $email = null,
                    public readonly ?string $phone = null,
                    public readonly ?string $address = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from([
                'name' => 'John',
                'email' => '',
                'phone' => '555-1234',
                'address' => '   ',
            ]);

            expect($data->name)->toBe('John');
            expect($data->email)->toBeNull();
            expect($data->phone)->toBe('555-1234');
            expect($data->address)->toBeNull();
        });
    });

    describe('Sad Paths', function (): void {
        test('preserves non-empty strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['name' => 'John Doe']);

            expect($data->name)->toBe('John Doe');
        });

        test('preserves strings with leading whitespace and content', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['name' => '  John']);

            expect($data->name)->toBe('  John');
        });

        test('preserves strings with trailing whitespace and content', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['name' => 'John  ']);

            expect($data->name)->toBe('John  ');
        });

        test('skips non-string values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    public readonly ?float $price = null,
                    public readonly bool $is_active = false,
                    public readonly ?array $tags = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from([
                'count' => 0,
                'price' => 0.0,
                'is_active' => false,
                'tags' => [],
            ]);

            expect($data->count)->toBe(0);
            expect($data->price)->toBe(0.0);
            expect($data->is_active)->toBeFalse();
            expect($data->tags)->toBe([]);
        });

        test('skips null values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['name' => null]);

            expect($data->name)->toBeNull();
        });

        test('skips properties not defined in data class', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from([
                'name' => 'John',
                'undefined_field' => '',
            ]);

            expect($data->name)->toBe('John');
        });
    });

    describe('Edge Cases', function (): void {
        test('handles unicode whitespace characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['name' => "\u{00A0}\u{2000}\u{2001}"]);

            expect($data->name)->toBeNull();
        });

        test('handles zero-width space characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['name' => "\u{200B}\u{200C}\u{200D}"]);

            expect($data->name)->toBeNull();
        });

        test('preserves single space string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['name' => ' ']);

            expect($data->name)->toBeNull();
        });

        test('handles string with only carriage return', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $text = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['text' => "\r\r"]);

            expect($data->text)->toBeNull();
        });

        test('handles string with zero character', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['name' => '0']);

            expect($data->name)->toBe('0');
        });

        test('handles string with false keyword', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $value = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['value' => 'false']);

            expect($data->value)->toBe('false');
        });

        test('handles multibyte empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['name' => '　　']); // Full-width spaces

            expect($data->name)->toBeNull();
        });

        test('preserves multibyte content with whitespace', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $name = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['name' => '  こんにちは  ']);

            expect($data->name)->toBe('  こんにちは  ');
        });

        test('handles very long whitespace string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $text = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['text' => str_repeat(' ', 1_000)]);

            expect($data->text)->toBeNull();
        });

        test('handles string with emoji', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $value = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['value' => '🚀']);

            expect($data->value)->toBe('🚀');
        });

        test('handles string with special characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $value = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['value' => '@#$%']);

            expect($data->value)->toBe('@#$%');
        });

        test('handles combination of whitespace types', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $text = null,
                ) {}

                public static function pipeline(): DataPipeline
                {
                    return parent::pipeline()
                        ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
                }
            };

            $data = $dataClass::from(['text' => " \t\n\r\u{00A0}\u{2000} "]);

            expect($data->text)->toBeNull();
        });
    });
});
