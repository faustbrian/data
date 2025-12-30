[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

This library provides extended validation attributes, casts, transformers, and type utilities for [spatie/laravel-data](https://github.com/spatie/laravel-data). It enhances the powerful data transfer object pattern with additional validation rules, type conversions, and utilities designed for Laravel 11+ with PHP 8.4+.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/) and [Laravel 11+](https://laravel.com/docs/11.x)**
>
> **Requires [spatie/laravel-data 4.18+](https://spatie.be/docs/laravel-data/v4/introduction)**

## Installation

```bash
composer require cline/data
```

## Documentation

- **[Validation Attributes](cookbook/validation-attributes.md)** - Comprehensive conditional and relational validation rules
- **[Casts and Transformers](cookbook/casts-and-transformers.md)** - Type conversion and data transformation utilities
- **[Type Utilities](cookbook/type-utilities.md)** - Type coercion and conversion utilities (StringLike, BooleanLike, NumberLike)
- **[Examples](cookbook/examples.md)** - Real-world usage examples and patterns

## Quick Start

### Using Validation Attributes

```php
use Cline\Data\Attributes\Validation\{PresentIf, MissingIf};
use Spatie\LaravelData\Data;

class OrderData extends Data
{
    public function __construct(
        #[PresentIf('type', 'premium')]
        public ?string $upgradeOption = null,

        #[MissingIf('automatic_renewal', true)]
        public ?int $renewalDays = null,
    ) {}
}
```

### Using Casts and Transformers

```php
use Cline\Data\Casts\{TrimCast, UpperCaseCast, NumberLikeCast};
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        #[TrimCast, UpperCaseCast]
        public string $name,

        #[NumberLikeCast]
        public int $accountId,
    ) {}
}
```

### Using Type Utilities

```php
use Cline\Data\Types\{StringLike, BooleanLike, NumberLike};

// Coerce values to appropriate types
$stringValue = StringLike::coerce('hello');
$boolValue = BooleanLike::coerce('true');
$numberValue = NumberLike::coerce('42.5');
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please use the [GitHub security reporting form][link-security] rather than the issue queue.

## Credits

- [Brian Faust][link-maintainer]
- [All Contributors][link-contributors]

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

[ico-tests]: https://git.cline.sh/faustbrian/data/actions/workflows/quality-assurance.yaml/badge.svg
[ico-version]: https://img.shields.io/packagist/v/cline/data.svg
[ico-license]: https://img.shields.io/badge/License-MIT-green.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cline/data.svg

[link-tests]: https://git.cline.sh/faustbrian/data/actions
[link-packagist]: https://packagist.org/packages/cline/data
[link-downloads]: https://packagist.org/packages/cline/data
[link-security]: https://git.cline.sh/faustbrian/data/security
[link-maintainer]: https://git.cline.sh/faustbrian
[link-contributors]: ../../contributors
