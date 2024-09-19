<?php

use Illuminate\Support\Facades\Http;
use UserCheck\Laravel\Exceptions\ApiRequestException;
use UserCheck\Laravel\Rules\UserCheck;
use UserCheck\Laravel\UserCheckService;

test('UserCheckRule passes when email is valid and not disposable', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheck(new UserCheckService);
    $fails = false;
    $rule->validate('email', 'test@example.com', function () use (&$fails) {
        $fails = true;
    });

    expect($fails)->toBeFalse();
});

test('UserCheckRule passes when email is disposable but block_disposable is not set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheck(new UserCheckService);
    $fails = false;
    $rule->validate('email', 'disposable@example.com', function () use (&$fails) {
        $fails = true;
    });

    expect($fails)->toBeFalse();
});

test('UserCheckRule fails when email is disposable and block_disposable is set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheck(new UserCheckService, ['block_disposable']);
    $failMessage = '';
    $rule->validate('email', 'disposable@example.com', function ($message) use (&$failMessage) {
        $failMessage = $message;
    });

    expect($failMessage)->toBe(trans('usercheck::validation.usercheck_disposable', ['attribute' => 'email']));
});

test('UserCheckRule fails when public domain is blocked', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => true,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheck(new UserCheckService, ['block_public_domain']);
    $failMessage = '';
    $rule->validate('email', 'test@gmail.com', function ($message) use (&$failMessage) {
        $failMessage = $message;
    });

    expect($failMessage)->toBe(trans('usercheck::validation.usercheck_public_domain', ['attribute' => 'email']));
});

test('UserCheckRule fails when domain has no MX records and block_no_mx is set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => false,
        ], 200),
    ]);

    $rule = new UserCheck(new UserCheckService, ['block_no_mx']);
    $failMessage = '';
    $rule->validate('email', 'test@example.com', function ($message) use (&$failMessage) {
        $failMessage = $message;
    });

    expect($failMessage)->toBe(trans('usercheck::validation.usercheck_no_mx', ['attribute' => 'email']));
});

test('UserCheckRule passes for domain-only validation', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheck(new UserCheckService, ['domain_only']);
    $fails = false;
    $rule->validate('domain', 'example.com', function () use (&$fails) {
        $fails = true;
    });

    expect($fails)->toBeFalse();
});

test('UserCheckRule passes for disposable domain in domain-only validation when block_disposable is not set', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheck(new UserCheckService, ['domain_only']);
    $fails = false;
    $rule->validate('domain', 'disposable.com', function () use (&$fails) {
        $fails = true;
    });

    expect($fails)->toBeFalse();
});

test('UserCheckRule fails for disposable domain in domain-only validation when block_disposable is set', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheck(new UserCheckService, ['domain_only', 'block_disposable']);
    $failMessage = '';
    $rule->validate('domain', 'disposable.com', function ($message) use (&$failMessage) {
        $failMessage = $message;
    });

    expect($failMessage)->toBe(trans('usercheck::validation.usercheck_disposable', ['attribute' => 'domain']));
});

test('UserCheckRule throws exception on API error', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response('Server error', 500),
    ]);

    $rule = new UserCheck(new UserCheckService);

    expect(fn () => $rule->validate('email', 'test@example.com', function () {}))
        ->toThrow(ApiRequestException::class, 'Unable to verify email: Server error');
});
