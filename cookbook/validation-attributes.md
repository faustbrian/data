# Validation Attributes

All attributes below are in the `Cline\Data\Attributes\Validation` namespace and extend spatie/laravel-data's validation system.

These attributes provide conditional and relational validation rules that make it easy to express complex business logic directly in your Data Transfer Objects.

## Present/Missing Conditions

### Presence Validation

Validates whether a field should be present based on conditions:

- `Present()` - Field must be present (not null)
- `Missing()` - Field must be missing or null

```php
use Cline\Data\Attributes\Validation\{Present, Missing};
use Spatie\LaravelData\Data;

class PaymentData extends Data
{
    public function __construct(
        #[Present]
        public string $cardNumber,

        #[Missing]
        public ?string $giftCardCode = null,
    ) {}
}
```

### Conditional Presence

These attributes make a field's requirement dependent on another field's value:

- `PresentIf(fieldName, value)` - Field must be present if another field equals value
- `PresentUnless(fieldName, value)` - Field must be present unless another field equals value
- `MissingIf(fieldName, value)` - Field must be missing if another field equals value
- `MissingUnless(fieldName, value)` - Field must be missing unless another field equals value

```php
use Cline\Data\Attributes\Validation\{PresentIf, MissingIf, PresentUnless};
use Spatie\LaravelData\Data;

class OrderData extends Data
{
    public function __construct(
        public string $type, // 'premium' or 'standard'

        #[PresentIf('type', 'premium')]
        public ?string $priorityLevel = null,

        #[MissingIf('type', 'standard')]
        public ?string $supportTier = null,

        #[PresentUnless('type', 'guest')]
        public ?string $accountId = null,
    ) {}
}
```

### Relational Presence

These attributes validate presence based on relationships with other fields:

- `PresentWith(...fields)` - Field must be present if any of the specified fields are present
- `PresentWithAll(...fields)` - Field must be present if all of the specified fields are present
- `MissingWith(...fields)` - Field must be missing if any of the specified fields are present
- `MissingWithAll(...fields)` - Field must be missing if all of the specified fields are present

```php
use Cline\Data\Attributes\Validation\{PresentWith, PresentWithAll, MissingWith, MissingWithAll};
use Spatie\LaravelData\Data;

class UserProfileData extends Data
{
    public function __construct(
        public string $name,

        #[PresentWith('country', 'state')]
        public ?string $zipCode = null,

        #[MissingWithAll('newsletter_opt_in', 'marketing_opt_in')]
        public ?string $unsubscribeReason = null,
    ) {}
}
```

## Numeric Validation

### Sign Validation

- `Positive()` - Value must be greater than zero
- `Negative()` - Value must be less than zero

```php
use Cline\Data\Attributes\Validation\{Positive, Negative};
use Spatie\LaravelData\Data;

class TemperatureData extends Data
{
    public function __construct(
        #[Positive]
        public float $celsiusAboveZero,

        #[Negative]
        public float $celsiusRise,
    ) {}
}
```

## Decimal Validation

- `Decimal(places)` - Validates that a numeric value has at most the specified number of decimal places

```php
use Cline\Data\Attributes\Validation\Decimal;
use Spatie\LaravelData\Data;

class PriceData extends Data
{
    public function __construct(
        #[Decimal(2)]
        public string $amount, // Must have max 2 decimal places: 19.99
    ) {}
}
```

## String Validation

### ASCII Validation

- `Ascii()` - String must contain only ASCII characters

```php
use Cline\Data\Attributes\Validation\Ascii;
use Spatie\LaravelData\Data;

class UsernameData extends Data
{
    public function __construct(
        #[Ascii]
        public string $username,
    ) {}
}
```

## Combining Attributes

You can combine multiple validation attributes on a single field for comprehensive validation:

```php
use Cline\Data\Attributes\Validation\{PresentIf, Decimal, Positive};
use Spatie\LaravelData\Data;

class DiscountData extends Data
{
    public function __construct(
        public string $discountType, // 'fixed' or 'percentage'

        #[
            PresentIf('discountType', 'percentage'),
            Decimal(2),
            Positive,
        ]
        public ?string $percentageAmount = null,

        #[
            PresentIf('discountType', 'fixed'),
            Positive,
        ]
        public ?float $fixedAmount = null,
    ) {}
}
```

## Best Practices

1. **Use descriptive field names** - Make it clear what each field represents
2. **Document complex rules** - Add comments explaining why certain validations exist
3. **Keep rules readable** - Avoid deeply nested conditions; consider splitting into smaller DTOs
4. **Test thoroughly** - Validation attributes can represent critical business logic

```php
class SubscriptionData extends Data
{
    public function __construct(
        public string $plan, // 'free', 'pro', 'enterprise'

        // Enterprise plans require contact information
        #[PresentIf('plan', 'enterprise')]
        public ?string $contactEmail = null,

        // Only auto-renewal for paid plans
        #[MissingIf('plan', 'free')]
        public ?bool $autoRenewal = null,
    ) {}
}
```
