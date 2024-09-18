<?php

use Illuminate\Support\Facades\Http;
use UserCheck\Laravel\Exceptions\ApiRequestException;
use UserCheck\Laravel\Rules\UserCheckRule;
use UserCheck\Laravel\UserCheckService;

test('UserCheckRule passes when email is valid and not disposable', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheckRule(new UserCheckService);

    expect($rule->passes('email', 'test@example.com'))->toBeTrue();
});

test('UserCheckRule passes when email is disposable but block_disposable is not set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheckRule(new UserCheckService);

    expect($rule->passes('email', 'disposable@example.com'))->toBeTrue();
});

test('UserCheckRule fails when email is disposable and block_disposable is set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheckRule(new UserCheckService, ['block_disposable']);

    expect($rule->passes('email', 'disposable@example.com'))->toBeFalse();
    expect($rule->message())->toBe(trans('usercheck::validation.usercheck_disposable', ['attribute' => 'email']));
});

test('UserCheckRule fails when public domain is blocked', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => true,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheckRule(new UserCheckService, ['block_public_domain']);

    expect($rule->passes('email', 'test@gmail.com'))->toBeFalse();
    expect($rule->message())->toBe(trans('usercheck::validation.usercheck_public_domain', ['attribute' => 'email']));
});

test('UserCheckRule fails when domain has no MX records and block_no_mx is set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => false,
        ], 200),
    ]);

    $rule = new UserCheckRule(new UserCheckService, ['block_no_mx']);

    expect($rule->passes('email', 'test@example.com'))->toBeFalse();
    expect($rule->message())->toBe(trans('usercheck::validation.usercheck_no_mx', ['attribute' => 'email']));
});

test('UserCheckRule passes for domain-only validation', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheckRule(new UserCheckService, ['domain_only']);

    expect($rule->passes('domain', 'example.com'))->toBeTrue();
});

test('UserCheckRule passes for disposable domain in domain-only validation when block_disposable is not set', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheckRule(new UserCheckService, ['domain_only']);

    expect($rule->passes('domain', 'disposable.com'))->toBeTrue();
});

test('UserCheckRule fails for disposable domain in domain-only validation when block_disposable is set', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $rule = new UserCheckRule(new UserCheckService, ['domain_only', 'block_disposable']);

    expect($rule->passes('domain', 'disposable.com'))->toBeFalse();
    expect($rule->message())->toBe(trans('usercheck::validation.usercheck_disposable', ['attribute' => 'domain']));
});

test('UserCheckRule throws exception on API error', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response('Server error', 500),
    ]);

    $rule = new UserCheckRule(new UserCheckService);

    expect(fn() => $rule->passes('email', 'test@example.com'))
        ->toThrow(ApiRequestException::class, 'Unable to verify email: Server error');
});
