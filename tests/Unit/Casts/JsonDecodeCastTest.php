<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\JsonDecodeCast;
use Spatie\LaravelData\Data;

describe('JsonDecodeCast', function (): void {
    describe('Happy Paths', function (): void {
        test('decodes JSON string to associative array by default', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $metadata = [],
                ) {}
            };

            $data = $dataClass::from(['metadata' => '{"name":"John","age":30}']);

            expect($data->metadata)->toBe(['name' => 'John', 'age' => 30]);
        });

        test('decodes JSON string with nested objects', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $config = [],
                ) {}
            };

            $data = $dataClass::from(['config' => '{"user":{"name":"Jane","email":"jane@example.com"}}']);

            expect($data->config)->toBe(['user' => ['name' => 'Jane', 'email' => 'jane@example.com']]);
        });

        test('decodes JSON array string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $tags = [],
                ) {}
            };

            $data = $dataClass::from(['tags' => '["php","laravel","testing"]']);

            expect($data->tags)->toBe(['php', 'laravel', 'testing']);
        });

        test('decodes JSON string to object when associative is false', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast(associative: false)]
                    public readonly object $metadata = new stdClass(),
                ) {}
            };

            $data = $dataClass::from(['metadata' => '{"name":"John","age":30}']);

            expect($data->metadata)->toBeObject()
                ->and($data->metadata->name)->toBe('John')
                ->and($data->metadata->age)->toBe(30);
        });

        test('passes through non-string values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $metadata = [],
                ) {}
            };

            $data = $dataClass::from(['metadata' => ['key' => 'value']]);

            expect($data->metadata)->toBe(['key' => 'value']);
        });

        test('decodes deeply nested JSON', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $data = [],
                ) {}
            };

            $data = $dataClass::from(['data' => '{"level1":{"level2":{"level3":"value"}}}']);

            expect($data->data)->toBe(['level1' => ['level2' => ['level3' => 'value']]]);
        });

        test('decodes empty JSON object', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $metadata = [],
                ) {}
            };

            $data = $dataClass::from(['metadata' => '{}']);

            expect($data->metadata)->toBe([]);
        });

        test('decodes empty JSON array', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $tags = [],
                ) {}
            };

            $data = $dataClass::from(['tags' => '[]']);

            expect($data->tags)->toBe([]);
        });
    });

    describe('Sad Paths', function (): void {
        test('throws exception for invalid JSON string', function (): void {
            expect(function (): void {
                $dataClass = new class() extends Data
                {
                    public function __construct(
                        #[JsonDecodeCast()]
                        public readonly array $metadata = [],
                    ) {}
                };

                $dataClass::from(['metadata' => '{invalid json}']);
            })->toThrow(JsonException::class);
        });

        test('throws exception for malformed JSON', function (): void {
            expect(function (): void {
                $dataClass = new class() extends Data
                {
                    public function __construct(
                        #[JsonDecodeCast()]
                        public readonly array $data = [],
                    ) {}
                };

                $dataClass::from(['data' => '{"key": "value"']);
            })->toThrow(JsonException::class);
        });

        test('throws exception for truncated JSON', function (): void {
            expect(function (): void {
                $dataClass = new class() extends Data
                {
                    public function __construct(
                        #[JsonDecodeCast()]
                        public readonly array $data = [],
                    ) {}
                };

                $dataClass::from(['data' => '{"name":"John",']);
            })->toThrow(JsonException::class);
        });

        test('throws exception for JSON with trailing comma', function (): void {
            expect(function (): void {
                $dataClass = new class() extends Data
                {
                    public function __construct(
                        #[JsonDecodeCast()]
                        public readonly array $data = [],
                    ) {}
                };

                $dataClass::from(['data' => '{"name":"John",}']);
            })->toThrow(JsonException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('decodes JSON with special characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $text = [],
                ) {}
            };

            $data = $dataClass::from(['text' => '{"message":"Hello\nWorld\ttab"}']);

            expect($data->text)->toBe(['message' => "Hello\nWorld\ttab"]);
        });

        test('decodes JSON with unicode characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $text = [],
                ) {}
            };

            $data = $dataClass::from(['text' => '{"emoji":"😀","japanese":"日本語"}']);

            expect($data->text)->toBe(['emoji' => '😀', 'japanese' => '日本語']);
        });

        test('decodes JSON with escaped quotes', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $text = [],
                ) {}
            };

            $data = $dataClass::from(['text' => '{"quote":"He said \"Hello\""}']);

            expect($data->text)->toBe(['quote' => 'He said "Hello"']);
        });

        test('decodes JSON with null values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $data = [],
                ) {}
            };

            $data = $dataClass::from(['data' => '{"name":"John","age":null}']);

            expect($data->data)->toBe(['name' => 'John', 'age' => null]);
        });

        test('decodes JSON with boolean values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $flags = [],
                ) {}
            };

            $data = $dataClass::from(['flags' => '{"enabled":true,"disabled":false}']);

            expect($data->flags)->toBe(['enabled' => true, 'disabled' => false]);
        });

        test('decodes JSON with numeric values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $numbers = [],
                ) {}
            };

            $data = $dataClass::from(['numbers' => '{"int":42,"float":3.14,"negative":-10}']);

            expect($data->numbers)->toBe(['int' => 42, 'float' => 3.14, 'negative' => -10]);
        });

        test('handles custom depth parameter', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast(depth: 3)]
                    public readonly array $data = [],
                ) {}
            };

            $data = $dataClass::from(['data' => '{"a":{"b":"value"}}']);

            expect($data->data)->toBe(['a' => ['b' => 'value']]);
        });

        test('decodes JSON string with whitespace', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly array $data = [],
                ) {}
            };

            $data = $dataClass::from(['data' => '  {"name":"John"}  ']);

            expect($data->data)->toBe(['name' => 'John']);
        });

        test('passes through integer unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly int $value = 0,
                ) {}
            };

            $data = $dataClass::from(['value' => 42]);

            expect($data->value)->toBe(42);
        });

        test('passes through null unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[JsonDecodeCast()]
                    public readonly mixed $value = null,
                ) {}
            };

            $data = $dataClass::from(['value' => null]);

            expect($data->value)->toBeNull();
        });
    });
});
