<?php

use Illuminate\Support\Facades\Http;
use UserCheck\Laravel\Facades\UserCheck;

test('UserCheck facade validateEmail method works', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $result = UserCheck::validateEmail('test@example.com');

    expect($result)->toBeArray()
        ->and($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('UserCheck facade validateEmail method allows disposable email by default', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $result = UserCheck::validateEmail('test@disposable.com');

    expect($result)->toBeArray()
        ->and($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('UserCheck facade validateEmail method detects disposable email when block_disposable is true', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $result = UserCheck::validateEmail('test@disposable.com', true);

    expect($result)->toBeArray()
        ->and($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('disposable');
});

test('UserCheck facade validateDomain method works', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $result = UserCheck::validateDomain('example.com');

    expect($result)->toBeArray()
        ->and($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('UserCheck facade validateDomain method allows disposable domain by default', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $result = UserCheck::validateDomain('disposable.com');

    expect($result)->toBeArray()
        ->and($result['is_valid'])->toBeTrue()
        ->and($result['error_code'])->toBeNull();
});

test('UserCheck facade validateDomain method detects disposable domain when block_disposable is true', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $result = UserCheck::validateDomain('disposable.com', true);

    expect($result)->toBeArray()
        ->and($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('disposable');
});

test('UserCheck facade validateEmail method detects public domain when blocked', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => true,
            'mx' => true,
        ], 200),
    ]);

    $result = UserCheck::validateEmail('test@gmail.com', false, false, true);

    expect($result)->toBeArray()
        ->and($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('public_domain');
});

test('UserCheck facade validateDomain method detects no MX records when blocked', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => false,
        ], 200),
    ]);

    $result = UserCheck::validateDomain('example.com', false, true);

    expect($result)->toBeArray()
        ->and($result['is_valid'])->toBeFalse()
        ->and($result['error_code'])->toBe('no_mx');
});
