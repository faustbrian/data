# Casts and Transformers

All casts below are in the `Cline\Data\Casts` namespace and extend spatie/laravel-data's cast system. Transformers are in the `Cline\Data\Transformers` namespace.

Casts transform data when creating or serializing Data Transfer Objects, while transformers handle more complex data conversions.

## String Manipulation Casts

### Case Transformation

- `UpperCaseCast` - Converts string to uppercase
- `LowerCaseCast` - Converts string to lowercase

```php
use Cline\Data\Casts\{UpperCaseCast, LowerCaseCast};
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        #[UpperCaseCast]
        public string $countryCode, // 'us' becomes 'US'

        #[LowerCaseCast]
        public string $email, // 'User@EXAMPLE.COM' becomes 'user@example.com'
    ) {}
}
```

### Whitespace Handling

- `TrimCast` - Removes leading and trailing whitespace

```php
use Cline\Data\Casts\TrimCast;
use Spatie\LaravelData\Data;

class FormData extends Data
{
    public function __construct(
        #[TrimCast]
        public string $name, // '  John Doe  ' becomes 'John Doe'

        #[TrimCast]
        public string $comment, // User input often has accidental whitespace
    ) {}
}
```

### URL Encoding

- `UrlEncodeCast` - URL encodes string values
- `UrlDecodeCast` - URL decodes string values

```php
use Cline\Data\Casts\{UrlEncodeCast, UrlDecodeCast};
use Spatie\LaravelData\Data;

class SearchData extends Data
{
    public function __construct(
        #[UrlEncodeCast]
        public string $query, // 'hello world' becomes 'hello+world'

        #[UrlDecodeCast]
        public string $decodedResult, // 'hello+world' becomes 'hello world'
    ) {}
}
```

## JSON Handling

- `JsonDecodeCast` - Decodes JSON string to array or object

```php
use Cline\Data\Casts\JsonDecodeCast;
use Spatie\LaravelData\Data;

class ConfigData extends Data
{
    public function __construct(
        #[JsonDecodeCast]
        public array $settings, // '{"theme":"dark"}' becomes ['theme' => 'dark']
    ) {}
}
```

## Array Transformation

- `ArrayToKeysCast` - Converts array to use its values as keys

```php
use Cline\Data\Casts\ArrayToKeysCast;
use Spatie\LaravelData\Data;

class PermissionsData extends Data
{
    public function __construct(
        #[ArrayToKeysCast]
        public array $roles, // ['admin', 'editor'] becomes ['admin' => null, 'editor' => null]
    ) {}
}
```

## Numeric Casts

### Rounding

- `RoundCast(decimals)` - Rounds number to specified decimal places

```php
use Cline\Data\Casts\RoundCast;
use Spatie\LaravelData\Data;

class PriceData extends Data
{
    public function __construct(
        #[RoundCast(2)]
        public float $amount, // 19.996 becomes 20.00
    ) {}
}
```

### Numeric Boundaries

- `CeilCast` - Rounds number up to nearest integer
- `FloorCast` - Rounds number down to nearest integer

```php
use Cline\Data\Casts\{CeilCast, FloorCast};
use Spatie\LaravelData\Data;

class ShippingData extends Data
{
    public function __construct(
        #[CeilCast]
        public int $boxesNeeded, // 3.2 becomes 4

        #[FloorCast]
        public int $completeBoxes, // 3.8 becomes 3
    ) {}
}
```

## Type Coercion Casts

- `NumberLikeCast` - Coerces values to numeric types (int/float)
- `BooleanLikeCast` - Coerces values to boolean (accepts 'true', '1', 'yes', etc.)

```php
use Cline\Data\Casts\{NumberLikeCast, BooleanLikeCast};
use Spatie\LaravelData\Data;

class SettingsData extends Data
{
    public function __construct(
        #[NumberLikeCast]
        public int $maxAttempts, // '5' becomes 5

        #[BooleanLikeCast]
        public bool $emailNotifications, // 'true' becomes true, 'false' becomes false
    ) {}
}
```

## Date/Time Casts

- `CarbonImmutableCast` - Casts to Carbon immutable datetime instance

```php
use Cline\Data\Casts\CarbonImmutableCast;
use Spatie\LaravelData\Data;

class EventData extends Data
{
    public function __construct(
        #[CarbonImmutableCast]
        public \Carbon\CarbonImmutable $startDate, // String becomes immutable datetime
    ) {}
}
```

## Combining Casts

Chain multiple casts on a single field for complex transformations:

```php
use Cline\Data\Casts\{TrimCast, UpperCaseCast};
use Spatie\LaravelData\Data;

class LocationData extends Data
{
    public function __construct(
        #[TrimCast, UpperCaseCast]
        public string $countryCode, // '  us  ' becomes 'US'

        #[TrimCast, LowerCaseCast]
        public string $cityName, // '  NEW YORK  ' becomes 'new york'
    ) {}
}
```

## Order of Cast Execution

Casts are applied in the order they are declared:

```php
use Cline\Data\Casts\{TrimCast, UpperCaseCast};

class Example extends Data
{
    public function __construct(
        #[TrimCast, UpperCaseCast] // First trim, then uppercase
        public string $value,
    ) {}
}
```

## Transformers

Transformers handle more complex data conversions beyond simple casting. They're typically used when you need conditional logic or access to the entire data object.

```php
use Cline\Data\Transformers\CustomTransformer;
use Spatie\LaravelData\Data;

class ComplexData extends Data
{
    public function __construct(
        #[CustomTransformer(MyTransformer::class)]
        public array $data, // Transformed by custom logic
    ) {}
}
```

## Best Practices

1. **Use casts for simple transformations** - Single responsibility principle
2. **Chain casts in logical order** - Trim before uppercasing, for example
3. **Document expected formats** - Clarify what values casts accept and produce
4. **Consider performance** - Complex transformations should be transformers, not chains of casts

```php
class RegistrationData extends Data
{
    public function __construct(
        #[TrimCast, UpperCaseCast]
        public string $countryCode, // Normalize country codes

        #[TrimCast, LowerCaseCast]
        public string $email, // Normalize email format

        #[NumberLikeCast]
        public int $age, // Ensure numeric type

        #[BooleanLikeCast]
        public bool $acceptsTerms, // Flexible boolean input
    ) {}
}
```
