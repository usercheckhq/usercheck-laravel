<?php

use Illuminate\Support\Facades\Http;
use UserCheck\Laravel\Exceptions\ApiRequestException;
use UserCheck\Laravel\UserCheckService;

test('validateEmail returns valid when email is valid and not disposable', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateEmail('test@example.com');

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('validateEmail returns valid when email is disposable and block_disposable is false', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateEmail('disposable@example.com');

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('validateEmail returns invalid when email is disposable and block_disposable is true', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateEmail('disposable@example.com', true);

    expect($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('disposable');
});

test('validateEmail returns invalid when public domain is blocked', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => true,
            'mx' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateEmail('test@gmail.com', false, false, true);

    expect($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('public_domain');
});

test('validateEmail returns valid when public domain is not blocked', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => true,
            'mx' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateEmail('test@gmail.com');

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('validateEmail returns invalid when domain has no MX records and blockNoMx is true', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => false,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateEmail('test@example.com', false, true);

    expect($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('no_mx');
});

test('validateEmail throws exception on API error', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response('Server error', 500),
    ]);

    $service = new UserCheckService;

    $this->expectException(ApiRequestException::class);
    $this->expectExceptionMessage('Unable to verify email: Server error');

    $service->validateEmail('test@example.com');
});

test('validateDomain returns valid when domain is valid and not disposable', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateDomain('example.com');

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('validateDomain returns valid when domain is disposable and block_disposable is false', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateDomain('disposable.com');

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('validateDomain returns invalid when domain is disposable and block_disposable is true', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateDomain('disposable.com', true);

    expect($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('disposable');
});

test('validateDomain returns invalid when public domain is blocked', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => true,
            'mx' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateDomain('gmail.com', false, false, true);

    expect($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('public_domain');
});

test('validateDomain returns valid when public domain is not blocked', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => true,
            'mx' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateDomain('gmail.com');

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('validateDomain throws exception on API error', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response('Server error', 500),
    ]);

    $service = new UserCheckService;

    $this->expectException(ApiRequestException::class);
    $this->expectExceptionMessage('Unable to verify domain: Server error');

    $service->validateDomain('example.com');
});

test('throws exception when API key is not set', function () {
    config(['usercheck.api_key' => null]);

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('UserCheck API key is not set.');

    new UserCheckService;
});
