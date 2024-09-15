# Laravel UserCheck

A Laravel package for validating email addresses using the UserCheck API.

## Installation

You can install the package via composer:

```bash
composer require yourname/laravel-usercheck
```

## Configuration
Publish the config file:

```
php artisan vendor:publish --provider="YourName\LaravelUserCheck\UserCheckServiceProvider" --tag="config"
```

Add your UserCheck API key to your .env file:

```
USERCHECK_API_KEY=your_api_key_here
```

## Usage

You can use the usercheck rule in your Laravel validation:

```
use YourName\LaravelUserCheck\Rules\UserCheck;

$request->validate([
    'email' => ['required', 'email', new UserCheck],
]);
```

Or use it as a string rule:

```
$request->validate([
    'email' => 'required|email|usercheck',
]);
```

## Testing

```
composer test
```

## License

```
The MIT License (MIT). Please see License File for more information.


11. Finally, commit your changes and push to a Git repository.

To use this package in a Laravel project, users would need to:

1. Install the package via Composer.
2. Add their UserCheck API key to their `.env` file.
3. Use the `usercheck` rule in their validation logic.

This package provides a simple way to integrate UserCheck validation into Laravel applications, making it easy for developers to prevent disposable email addresses from being used in their systems.
```
