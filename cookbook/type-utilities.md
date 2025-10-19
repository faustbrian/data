# Type Utilities

All type utilities below are in the `Cline\Data\Types` namespace. These classes provide flexible type coercion and conversion capabilities for handling various input formats.

## Overview

Type utilities handle the conversion of loosely-typed PHP values into strict types. They're useful when dealing with user input, API responses, or configuration data that may come in different formats but needs to be coerced to a specific type.

## StringLike

The `StringLike` class provides string coercion from various input types.

### Basic Usage

```php
use Cline\Data\Types\StringLike;

StringLike::coerce('hello');           // 'hello'
StringLike::coerce(123);               // '123'
StringLike::coerce(45.67);             // '45.67'
StringLike::coerce(true);              // 'true'
StringLike::coerce(false);             // 'false'
StringLike::coerce(['a', 'b']);        // 'a,b' (serializes array)
StringLike::coerce(null);              // '' (empty string)
```

### Use Cases

- Normalizing identifiers from various sources
- Converting API responses to strings
- Flexible configuration values

```php
use Cline\Data\Types\StringLike;
use Spatie\LaravelData\Data;

class ApiKeyData extends Data
{
    public function __construct(
        public string $apiKey,
    ) {}

    public static function fromInput($input): self
    {
        return new self(
            apiKey: StringLike::coerce($input['key']), // Accepts any type
        );
    }
}

// Works with various inputs
$data1 = ApiKeyData::fromInput(['key' => 'abc123']);
$data2 = ApiKeyData::fromInput(['key' => 123]); // Coerces to '123'
$data3 = ApiKeyData::fromInput(['key' => ['prefix', 'suffix']]); // Becomes 'prefix,suffix'
```

## BooleanLike

The `BooleanLike` class provides intelligent boolean coercion from various input formats.

### Basic Usage

```php
use Cline\Data\Types\BooleanLike;

// Truthy values
BooleanLike::coerce(true);             // true
BooleanLike::coerce(1);                // true
BooleanLike::coerce('1');              // true
BooleanLike::coerce('true');           // true
BooleanLike::coerce('yes');            // true
BooleanLike::coerce('on');             // true

// Falsy values
BooleanLike::coerce(false);            // false
BooleanLike::coerce(0);                // false
BooleanLike::coerce('0');              // false
BooleanLike::coerce('false');          // false
BooleanLike::coerce('no');             // false
BooleanLike::coerce('off');            // false
BooleanLike::coerce('');               // false
BooleanLike::coerce(null);             // false
```

### Use Cases

- Parsing HTML form checkboxes (often sent as '1' or '0')
- Processing environment variables
- API requests with flexible boolean parameters

```php
use Cline\Data\Types\BooleanLike;
use Spatie\LaravelData\Data;

class FeatureFlags extends Data
{
    public function __construct(
        public bool $darkMode,
        public bool $analytics,
        public bool $betaFeatures,
    ) {}

    public static function fromEnvironment(): self
    {
        return new self(
            darkMode: BooleanLike::coerce(env('DARK_MODE', false)),
            analytics: BooleanLike::coerce(env('ENABLE_ANALYTICS', 'yes')),
            betaFeatures: BooleanLike::coerce(env('BETA_FEATURES', '0')),
        );
    }
}

// Handles various input formats gracefully
$flags = FeatureFlags::fromEnvironment();
// env('DARK_MODE', false) could be 'true', 'yes', '1', or false
// env('ENABLE_ANALYTICS', 'yes') could be 'yes', 'true', 1, or true
// env('BETA_FEATURES', '0') could be '0', 'false', 'no', or 0
```

## NumberLike

The `NumberLike` class provides numeric coercion for int and float values.

### Basic Usage

```php
use Cline\Data\Types\NumberLike;

// Integer coercion
NumberLike::coerce('123');             // 123
NumberLike::coerce('123.99');          // 123 (truncates decimal)
NumberLike::coerce(true);              // 1
NumberLike::coerce(false);             // 0
NumberLike::coerce(null);              // 0

// Float preservation
NumberLike::coerce('45.67');           // 45.67
NumberLike::coerce(45.67);             // 45.67
```

### Use Cases

- Parsing numeric strings from user input
- Converting API response data
- Type-safe configuration values

```php
use Cline\Data\Types\NumberLike;
use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public int $quantity,
        public float $price,
        public int $stock,
    ) {}

    public static function fromApiResponse(array $response): self
    {
        return new self(
            quantity: NumberLike::coerce($response['qty']),      // Could be string
            price: NumberLike::coerce($response['price']),       // Could be int or string
            stock: NumberLike::coerce($response['available']),   // Could be string
        );
    }
}

// Handles flexible input
$product = ProductData::fromApiResponse([
    'qty' => '5',              // String
    'price' => 19.99,          // Float
    'available' => '100',      // String
]);

echo $product->quantity;       // 5 (int)
echo $product->price;          // 19.99 (float)
echo $product->stock;          // 100 (int)
```

## Combining with Data Classes

Type utilities work seamlessly with spatie/laravel-data:

```php
use Cline\Data\Types\{StringLike, BooleanLike, NumberLike};
use Spatie\LaravelData\Data;

class FlexibleInputData extends Data
{
    public function __construct(
        public string $name,
        public int $count,
        public bool $active,
    ) {}

    public static function coerce(array $input): self
    {
        return new self(
            name: StringLike::coerce($input['name'] ?? ''),
            count: NumberLike::coerce($input['count'] ?? 0),
            active: BooleanLike::coerce($input['active'] ?? false),
        );
    }
}

// All these inputs work seamlessly
$data1 = FlexibleInputData::coerce([
    'name' => 'Product',
    'count' => '10',
    'active' => 'yes',
]);

$data2 = FlexibleInputData::coerce([
    'name' => ['product', 'name'],
    'count' => 10.5,
    'active' => 1,
]);

$data3 = FlexibleInputData::coerce([
    'name' => 123,
    'count' => '5',
    'active' => true,
]);
```

## Error Handling

Type utilities coerce values gracefully and rarely throw errors. Invalid or unexpected inputs are handled with sensible defaults:

```php
use Cline\Data\Types\NumberLike;

// Edge cases are handled gracefully
NumberLike::coerce('abc');             // 0 (non-numeric string)
NumberLike::coerce([]);                // 0 (empty array)
NumberLike::coerce(new stdClass());    // 0 (object)
```

## Best Practices

1. **Use in factory methods** - Create dedicated methods for flexible input handling
2. **Document expected formats** - Be clear about what types your DTO accepts
3. **Validate after coercion** - Use validation attributes to ensure data correctness
4. **Consider strictness** - Use strict casting for APIs; type utilities for user input

```php
use Cline\Data\Types\{StringLike, NumberLike, BooleanLike};
use Cline\Data\Attributes\Validation\{Present, Positive};
use Spatie\LaravelData\Data;

class OrderData extends Data
{
    public function __construct(
        #[Present]
        public string $itemName,

        #[Positive]
        public int $quantity,

        public bool $express,
    ) {}

    /**
     * Creates order from flexible user input
     * Accepts various formats and coerces to proper types
     */
    public static function fromUserInput(array $input): self
    {
        return new self(
            itemName: StringLike::coerce($input['item'] ?? ''),
            quantity: NumberLike::coerce($input['qty'] ?? 0),
            express: BooleanLike::coerce($input['express'] ?? false),
        );
    }
}
```
