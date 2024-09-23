## UserCheck for Laravel

[![Latest Stable Version](https://img.shields.io/packagist/v/usercheck/usercheck-laravel.svg?style=flat-square)](https://packagist.org/packages/usercheck/usercheck-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/usercheck/usercheck-laravel.svg?style=flat-square)](https://packagist.org/packages/usercheck/usercheck-laravel)
[![License](https://img.shields.io/packagist/l/usercheck/usercheck-laravel.svg?style=flat-square)](https://packagist.org/packages/usercheck/usercheck-laravel)
[![Tests Status](https://img.shields.io/github/actions/workflow/status/usercheckhq/laravel/tests.yml?label=tests&branch=main&style=flat-square)](https://github.com/usercheckhq/laravel/actions)

A Laravel package for validating email addresses using the [UserCheck.com](https://www.usercheck.com) API.  

## ✨ Features

- Block disposable email addresses with an always up-to-date API
- Validate email addresses
- Check for MX records
- Identify personal email addresses
- Customizable validation rules
- Laravel Facade for easy use
- Localization support

## Requirements

- PHP 8.0+
- Laravel 11.0+

## Installation

Install the package via Composer:

```bash
composer require usercheck/usercheck-laravel
```

## Configuration

Add your UserCheck API key to your `.env` file:

```bash
USERCHECK_API_KEY=your_api_key_here
```

You can obtain a free API key by signing up at [https://app.usercheck.com/register](https://app.usercheck.com/register).

## Usage

Use the `usercheck` rule in your Laravel validation:

```php
$request->validate([
    'email' => 'required|email|usercheck'
]);
```

### Options

By default, the `usercheck` rule will only validate the email address's syntax using the UserCheck API. If the email is invalid, the validation will fail.

The `usercheck` rule accepts several parameters:

- `block_disposable`: Fails validation if the email is from a disposable email provider
- `block_no_mx`: Fails validation if the domain has no MX records
- `block_public_domain`: Fails validation for public email domains (e.g., Gmail, Yahoo). Great to prevent users from signing up with their personal email addresses.
- `domain_only`: Validates only the domain part of the email. Great for privacy; only the domain will be sent to the API.

You can combine these options to create a custom validation rule.

```php
$request->validate([
    'email' => 'required|email|usercheck:domain_only,block_disposable,block_no_mx',
]);
```

### Using the Facade

You can also use the UserCheck facade directly:

```php
use UserCheck\Laravel\Facades\UserCheck;
$result = UserCheck::validateEmail('test@example.com');
$result = UserCheck::validateDomain('example.com');
```

Both methods return an array with `is_valid` and `error_code` keys.

## Localization

The package includes English translations by default. To customize the error messages, publish the language files:

```bash
php artisan vendor:publish --provider="UserCheck\Laravel\UserCheckProvider" --tag="lang"
```

Then, edit the files in `resources/lang/vendor/usercheck`.

## Testing

Run the tests with:

```bash
composer test
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email [security@usercheck.com](mailto:security@usercheck.com) instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For support, please email [support@usercheck.com](mailto:support@usercheck.com) or open an issue on GitHub.
