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

test('validateEmail returns invalid when email is blocklisted', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateEmail('test@blocklisted.com', false, false, false, true);

    expect($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('blocklisted');
});

test('validateEmail returns valid when email is blocklisted but block_blocklisted is false', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateEmail('test@blocklisted.com');

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('validateDomain returns invalid when domain is blocklisted', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateDomain('blocklisted.com', false, false, false, true);

    expect($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('blocklisted');
});

test('validateDomain returns valid when domain is blocklisted but block_blocklisted is false', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateDomain('blocklisted.com');

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

// Invalid Input Tests
test('validateEmail handles null input by throwing TypeError', function () {
    $service = new UserCheckService;

    expect(fn () => $service->validateEmail(null))
        ->toThrow(TypeError::class);
});

test('validateEmail handles array input by throwing TypeError', function () {
    $service = new UserCheckService;

    expect(fn () => $service->validateEmail(['test@example.com']))
        ->toThrow(TypeError::class);
});

test('validateEmail handles empty string input', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([], 400),
    ]);

    $service = new UserCheckService;
    $result = $service->validateEmail('');

    expect($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('usercheck');
});

test('validateEmail handles extremely long email addresses', function () {
    $longLocalPart = str_repeat('a', 64); // RFC 5321 limit
    $longDomain = str_repeat('b', 255); // Maximum domain length
    $longEmail = "{$longLocalPart}@{$longDomain}.com";

    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $service = new UserCheckService;
    $result = $service->validateEmail($longEmail);

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('validateEmail handles multiple @ symbols by returning invalid', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([], 400),
    ]);

    $service = new UserCheckService;
    $result = $service->validateEmail('test@multiple@example.com');

    expect($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('usercheck');
});

test('validateEmail handles missing blocklisted parameter in API response when block_blocklisted is true', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            // blocklisted parameter is missing
        ], 200),
    ]);

    $service = new UserCheckService;
    $result = $service->validateEmail('test@example.com', false, false, false, true);

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('validateDomain handles missing blocklisted parameter in API response when block_blocklisted is true', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            // blocklisted parameter is missing
        ], 200),
    ]);

    $service = new UserCheckService;
    $result = $service->validateDomain('example.com', false, false, false, true);

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('validateEmail returns invalid when email is from relay domain and block_relay_domain is true', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'relay_domain' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateEmail('test@relay.com', false, false, false, false, true);

    expect($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('relay_domain');
});

test('validateEmail returns valid when email is from relay domain but block_relay_domain is false', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'relay_domain' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateEmail('test@relay.com');

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('validateDomain returns invalid when domain is relay domain and block_relay_domain is true', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'relay_domain' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateDomain('relay.com', false, false, false, false, true);

    expect($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('relay_domain');
});

test('validateDomain returns valid when domain is relay domain but block_relay_domain is false', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'relay_domain' => true,
        ], 200),
    ]);

    $service = new UserCheckService;

    $result = $service->validateDomain('relay.com');

    expect($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});
