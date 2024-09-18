## Laravel UserCheck

A Laravel package for validating email addresses and domains using the [UserCheck.com](https://www.usercheck.com) API.  
This package provides an easy way to integrate email and domain validation into your Laravel application, helping you prevent disposable or invalid email addresses from being used.

## Features

- Validate email addresses and domains
- Detect disposable email addresses
- Check for MX records
- Identify public email domains (e.g., Gmail, Yahoo)
- Customizable validation rules
- Laravel Facade for easy use
- Localization support

## Requirements

- PHP 8.0+
- Laravel 11.0+

## Installation

Install the package via Composer:

```bash
composer require usercheck/laravel
```

## Configuration

1.  Add your UserCheck API key to your `.env` file:

```bash
USERCHECK_API_KEY=your_api_key_here
```

You can obtain a free API key by signing up at [https://app.usercheck.com/register](https://app.usercheck.com).

## Usage

### As a Validation Rule

You can use the `usercheck` rule in your Laravel validation:

```php
$request->validate([
    'email' => 'required|email|usercheck'
]);
```

This rule will validate the email address's syntax using the UserCheck API. If the email is invalid, the validation will fail.

### Additional Options

The `usercheck` rule accepts several options:

- `domain_only`: Validates only the domain part of the email. Only the domain will be sent to the UserCheck API.
- `block_disposable`: Fails validation if the email is from a disposable email provider
- `block_no_mx`: Fails validation if the domain has no MX records
- `block_public_domain`: Fails validation for public email domains (e.g., Gmail, Yahoo)

Example:

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
