# Examples

Real-world usage examples demonstrating how all components work together. These examples show common patterns and best practices for using the data package.

## E-Commerce Order Processing

This example shows a complete order processing data structure with validation, casts, and type utilities:

```php
use Cline\Data\Attributes\Validation\{Present, Positive, Decimal, PresentIf, MissingIf};
use Cline\Data\Casts\{TrimCast, UpperCaseCast, NumberLikeCast, RoundCast};
use Spatie\LaravelData\Data;

class OrderData extends Data
{
    public function __construct(
        #[Present, TrimCast]
        public string $orderNumber,

        #[
            Present,
            TrimCast,
            UpperCaseCast,
        ]
        public string $countryCode, // 'us' becomes 'US'

        #[Positive]
        public float $subtotal,

        #[Decimal(2)]
        public string $taxRate, // Must be like '0.08' or '0.085'

        #[
            PresentIf('countryCode', 'US'),
            Positive,
        ]
        public ?float $shippingCost = null,

        #[
            MissingIf('subtotal', 0),
            Decimal(2),
        ]
        public ?string $discount = null,
    ) {}

    public static function fromCheckout(array $input): self
    {
        return new self(
            orderNumber: $input['order_id'] ?? '',
            countryCode: $input['country'] ?? '', // Normalized by UpperCaseCast
            subtotal: (float)$input['subtotal'],
            taxRate: $input['tax_rate'] ?? '0',
            shippingCost: isset($input['country']) && $input['country'] === 'US'
                ? (float)$input['shipping'] ?? 0
                : null,
            discount: $input['discount'] ?? null,
        );
    }

    public function total(): float
    {
        $tax = $this->subtotal * (float)$this->taxRate;
        $shipping = $this->shippingCost ?? 0;
        $discount = (float)($this->discount ?? 0);

        return $this->subtotal + $tax + $shipping - $discount;
    }
}

// Usage
$order = OrderData::fromCheckout([
    'order_id' => '  ORD-2024-001  ', // Trimmed
    'country' => 'us',                 // Uppercased to 'US'
    'subtotal' => '99.99',
    'tax_rate' => '0.08',
    'shipping' => '10.00',
    'discount' => null,
]);

echo $order->total(); // Calculated total
```

## User Registration and Profile

A practical example of user registration with flexible input and comprehensive validation:

```php
use Cline\Data\Attributes\Validation\{Present, PresentUnless, Ascii};
use Cline\Data\Casts\{TrimCast, LowerCaseCast, BooleanLikeCast};
use Cline\Data\Types\{StringLike, BooleanLike};
use Spatie\LaravelData\Data;

class UserRegistrationData extends Data
{
    public function __construct(
        #[Present, TrimCast, Ascii]
        public string $name,

        #[Present, TrimCast, LowerCaseCast]
        public string $email,

        #[
            PresentUnless('socialProvider', 'google'),
            Present,
        ]
        public ?string $password = null,

        #[BooleanLikeCast]
        public bool $acceptsTerms,

        #[BooleanLikeCast]
        public bool $subscribesNewsletter = false,

        public ?string $socialProvider = null,
    ) {}

    /**
     * Create from HTML form submission
     * Form data often comes with inconsistent types
     */
    public static function fromFormSubmission(array $form): self
    {
        return new self(
            name: StringLike::coerce($form['name'] ?? ''),
            email: StringLike::coerce($form['email'] ?? ''),
            password: $form['password'] ?? null,
            acceptsTerms: BooleanLike::coerce($form['terms_accepted'] ?? false),
            subscribesNewsletter: BooleanLike::coerce($form['newsletter'] ?? false),
            socialProvider: $form['social_provider'] ?? null,
        );
    }

    /**
     * Create from social login
     * Social providers supply verified email
     */
    public static function fromSocialAuth(string $provider, array $profile): self
    {
        return new self(
            name: StringLike::coerce($profile['name'] ?? ''),
            email: StringLike::coerce($profile['email'] ?? ''),
            password: null,
            acceptsTerms: true, // Implicit acceptance
            subscribesNewsletter: BooleanLike::coerce($profile['subscribed'] ?? false),
            socialProvider: $provider,
        );
    }
}

// HTML form usage - checkbox sends '1' or missing
$user = UserRegistrationData::fromFormSubmission([
    'name' => '  John Doe  ',           // Will be trimmed
    'email' => 'JOHN@EXAMPLE.COM',      // Will be lowercased
    'password' => 'secret123',
    'terms_accepted' => '1',            // Form checkbox value
    'newsletter' => 'on',               // Flexible boolean
    'social_provider' => null,
]);

// Social auth usage
$socialUser = UserRegistrationData::fromSocialAuth('google', [
    'name' => 'Jane Smith',
    'email' => 'jane@gmail.com',
    'subscribed' => true,
]);
```

## API Request Data Transfer

Working with flexible external API data:

```php
use Cline\Data\Attributes\Validation\{Positive, Decimal, PresentWith};
use Cline\Data\Casts\{NumberLikeCast, JsonDecodeCast};
use Cline\Data\Types\{NumberLike, StringLike};
use Spatie\LaravelData\Data;

class PaymentIntentData extends Data
{
    public function __construct(
        #[Positive]
        public float $amount,

        #[Decimal(2)]
        public string $currency,

        #[PresentWith('metadata')]
        public ?array $description = null,

        #[JsonDecodeCast]
        public ?array $metadata = null,
    ) {}

    /**
     * Parse flexible API response
     * Different payment providers return data in different formats
     */
    public static function fromPaymentProvider(array $response): self
    {
        return new self(
            amount: NumberLike::coerce($response['amount'] ?? 0),
            currency: strtoupper(StringLike::coerce($response['currency'] ?? 'USD')),
            description: $response['description'] ?? null,
            metadata: is_string($response['metadata'] ?? null)
                ? json_decode($response['metadata'], true)
                : $response['metadata'] ?? null,
        );
    }
}

// Handles various API response formats
$payment1 = PaymentIntentData::fromPaymentProvider([
    'amount' => '99.99',                      // String
    'currency' => 'usd',                      // Lowercase
    'metadata' => '{"order_id": "12345"}',    // JSON string
]);

$payment2 = PaymentIntentData::fromPaymentProvider([
    'amount' => 99.99,                        // Float
    'currency' => 'USD',                      // Already uppercase
    'metadata' => ['order_id' => '12345'],    // Array
]);
```

## Configuration Data with Type Coercion

Flexible configuration handling from environment or config files:

```php
use Cline\Data\Types\{StringLike, BooleanLike, NumberLike};
use Cline\Data\Casts\{UpperCaseCast, NumberLikeCast};
use Spatie\LaravelData\Data;

class AppConfigData extends Data
{
    public function __construct(
        public string $appName,
        public bool $debugMode,
        public int $maxConnections,
        #[UpperCaseCast]
        public string $environment,
        public float $cacheExpire,
    ) {}

    /**
     * Load from environment variables
     * Environment variables are always strings
     */
    public static function fromEnvironment(): self
    {
        return new self(
            appName: StringLike::coerce(env('APP_NAME', 'MyApp')),
            debugMode: BooleanLike::coerce(env('APP_DEBUG', false)),
            maxConnections: NumberLike::coerce(env('DB_MAX_CONNECTIONS', '10')),
            environment: env('APP_ENV', 'production'),
            cacheExpire: NumberLike::coerce(env('CACHE_EXPIRE', '3600')),
        );
    }

    /**
     * Load from config array
     * Config files might have mixed types
     */
    public static function fromConfig(array $config): self
    {
        return new self(
            appName: StringLike::coerce($config['name'] ?? ''),
            debugMode: BooleanLike::coerce($config['debug'] ?? false),
            maxConnections: NumberLike::coerce($config['connections'] ?? 10),
            environment: $config['env'] ?? 'production',
            cacheExpire: NumberLike::coerce($config['cache']['expire'] ?? 3600),
        );
    }
}

// Environment usage - everything is a string
$config1 = AppConfigData::fromEnvironment();
// APP_DEBUG=true (string) → true (bool)
// DB_MAX_CONNECTIONS=20 (string) → 20 (int)

// Config file usage - mixed types
$config2 = AppConfigData::fromConfig([
    'name' => 'MyApp',
    'debug' => true,                // Could be bool
    'connections' => '20',          // Could be string
    'env' => 'local',
    'cache' => ['expire' => 7200],
]);
```

## Conditional Fields Based on Type

A practical example of model polymorphism:

```php
use Cline\Data\Attributes\Validation\{PresentIf, MissingIf, Present};
use Cline\Data\Casts\TrimCast;
use Spatie\LaravelData\Data;

class SubscriptionData extends Data
{
    public function __construct(
        #[Present]
        public string $planType, // 'free', 'pro', 'enterprise'

        #[
            MissingIf('planType', 'free'),
            Present,
        ]
        public ?string $billingEmail = null,

        #[PresentIf('planType', 'enterprise')]
        public ?string $accountManager = null,

        #[PresentIf('planType', 'enterprise')]
        public ?int $customSeats = null,

        #[
            PresentIf('planType', 'pro'),
            PresentIf('planType', 'enterprise'),
            TrimCast,
        ]
        public ?string $supportTier = null,
    ) {}
}

// Free plan - no billing needed
$free = new SubscriptionData(
    planType: 'free',
    billingEmail: null,
    accountManager: null,
    customSeats: null,
    supportTier: null,
);

// Pro plan - billing and standard support
$pro = new SubscriptionData(
    planType: 'pro',
    billingEmail: 'billing@company.com',
    accountManager: null,
    customSeats: null,
    supportTier: 'standard',
);

// Enterprise - full configuration
$enterprise = new SubscriptionData(
    planType: 'enterprise',
    billingEmail: 'billing@company.com',
    accountManager: 'Jane Smith',
    customSeats: 100,
    supportTier: 'premium',
);
```

## Best Practices Demonstrated

1. **Factory Methods** - Use static factory methods for different input sources
2. **Type Flexibility** - Accept various input types and coerce to strict types
3. **Validation** - Use attributes to express business rules
4. **Transformation** - Use casts for consistent data format
5. **Documentation** - Include docblocks explaining data sources and formats
