<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

test('usercheck validation rule passes when email is valid and not disposable', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'test@example.com'],
        ['email' => 'usercheck']
    );

    expect($validator->passes())->toBeTrue();
});

test('usercheck validation rule passes when email is disposable but block_disposable is not set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'disposable@example.com'],
        ['email' => 'usercheck']
    );

    expect($validator->passes())->toBeTrue();
});

test('usercheck validation rule fails when email is from a public domain and public domains are blocked', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => true,
            'mx' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'test@gmail.com'],
        ['email' => 'usercheck:block_public_domain']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(trans('usercheck::validation.usercheck_public_domain', ['attribute' => 'email']));
});

test('usercheck validation rule fails when email domain has no MX records and block_no_mx is set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => false,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'test@example.com'],
        ['email' => 'usercheck:block_no_mx']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(trans('usercheck::validation.usercheck_no_mx', ['attribute' => 'email']));
});

test('usercheck validation rule passes for valid domain when domain_only is set', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['domain' => 'example.com'],
        ['domain' => 'usercheck:domain_only']
    );

    expect($validator->passes())->toBeTrue();
});

test('usercheck validation rule passes for disposable domain when domain_only is set but block_disposable is not', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['domain' => 'disposable.com'],
        ['domain' => 'usercheck:domain_only']
    );

    expect($validator->passes())->toBeTrue();
});

test('usercheck validation rule fails for public domain when domain_only and block_public_domain are set', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => true,
            'mx' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['domain' => 'gmail.com'],
        ['domain' => 'usercheck:domain_only,block_public_domain']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('domain'))->toBe(trans('usercheck::validation.usercheck_public_domain', ['attribute' => 'domain']));
});

test('usercheck validation rule fails for domain with no MX when domain_only and block_no_mx are set', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => false,
        ], 200),
    ]);

    $validator = Validator::make(
        ['domain' => 'example.com'],
        ['domain' => 'usercheck:domain_only,block_no_mx']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('domain'))->toBe(trans('usercheck::validation.usercheck_no_mx', ['attribute' => 'domain']));
});

test('usercheck validation rule passes when email is not disposable and block_disposable is set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'test@example.com'],
        ['email' => 'usercheck:block_disposable']
    );

    expect($validator->passes())->toBeTrue();
});

test('usercheck validation rule fails when email is disposable and block_disposable is set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'disposable@example.com'],
        ['email' => 'usercheck:block_disposable']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(trans('usercheck::validation.usercheck_disposable', ['attribute' => 'email']));
});

test('usercheck validation rule passes when domain is not disposable and block_disposable is set with domain_only', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['domain' => 'example.com'],
        ['domain' => 'usercheck:domain_only,block_disposable']
    );

    expect($validator->passes())->toBeTrue();
});

test('usercheck validation rule fails when domain is disposable and block_disposable is set with domain_only', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['domain' => 'disposable.com'],
        ['domain' => 'usercheck:domain_only,block_disposable']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('domain'))->toBe(trans('usercheck::validation.usercheck_disposable', ['attribute' => 'domain']));
});

test('usercheck validation rule fails when API returns 400 status', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'status' => 400,
            'error' => 'The email is invalid.',
        ], 400),
    ]);

    $validator = Validator::make(
        ['email' => 'invalid@email'],
        ['email' => 'usercheck']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe('The email is invalid.');
});

test('usercheck validation rule fails when API returns 400 status for domain_only', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'status' => 400,
            'error' => 'The domain is invalid.',
        ], 400),
    ]);

    $validator = Validator::make(
        ['email' => 'test@gmailcom'],
        ['email' => 'usercheck:domain_only']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe('The email is invalid.');
});

test('usercheck validation rule fails when email is blocklisted and block_blocklisted is set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'test@blocklisted.com'],
        ['email' => 'usercheck:block_blocklisted']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(trans('usercheck::validation.usercheck_blocklisted', ['attribute' => 'email']));
});

test('usercheck validation rule passes when email is blocklisted but block_blocklisted is not set', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'test@blocklisted.com'],
        ['email' => 'usercheck']
    );

    expect($validator->passes())->toBeTrue();
});

test('usercheck validation rule fails when domain is blocklisted with domain_only and block_blocklisted', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['domain' => 'blocklisted.com'],
        ['domain' => 'usercheck:domain_only,block_blocklisted']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('domain'))->toBe(trans('usercheck::validation.usercheck_blocklisted', ['attribute' => 'domain']));
});

test('usercheck validation rule passes when domain is blocklisted with domain_only but without block_blocklisted', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['domain' => 'blocklisted.com'],
        ['domain' => 'usercheck:domain_only']
    );

    expect($validator->passes())->toBeTrue();
});

// Add these tests to ValidatorExtensionsTest.php

test('usercheck validation rule handles multiple blocklist scenarios correctly', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => true,
        ], 200),
    ]);

    // Test with both block_blocklisted and other flags
    $validator = Validator::make(
        ['email' => 'test@blocklisted.com'],
        ['email' => 'usercheck:block_blocklisted,block_disposable']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))
        ->toBe(trans('usercheck::validation.usercheck_blocklisted', ['attribute' => 'email']));

    // Test with custom error message
    $validator = Validator::make(
        ['email' => 'test@blocklisted.com'],
        ['email' => 'usercheck:block_blocklisted'],
        ['usercheck' => 'Custom blocklist message for :attribute']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))
        ->toBe('Custom blocklist message for email');
});

test('usercheck validation rule handles blocklisted subdomains correctly', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'test@subdomain.blocklisted.com'],
        ['email' => 'usercheck:block_blocklisted']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))
        ->toBe(trans('usercheck::validation.usercheck_blocklisted', ['attribute' => 'email']));
});

test('usercheck validation prioritizes blocklist error over other validation failures', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => true,
            'mx' => false,
            'blocklisted' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'test@blocklisted.com'],
        ['email' => ['required', 'email', 'usercheck:block_blocklisted,block_disposable,block_no_mx,block_public_domain']]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))
        ->toBe(trans('usercheck::validation.usercheck_blocklisted', ['attribute' => 'email']));
});

test('usercheck validation rule properly handles blocklisted domains with domain_only option', function () {
    // First test: Domain is blocklisted
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['domain' => 'blocklisted.com'],
        ['domain' => ['required', 'usercheck:domain_only,block_blocklisted']]
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('domain'))
        ->toBe(trans('usercheck::validation.usercheck_blocklisted', ['attribute' => 'domain']));
});

test('usercheck validation rule passes for non-blocklisted domains with domain_only option', function () {
    Http::fake([
        'https://api.usercheck.com/domain/*' => Http::response([
            'disposable' => false,
            'public_domain' => false,
            'mx' => true,
            'blocklisted' => false,
        ], 200),
    ]);

    $validator = Validator::make(
        ['domain' => 'example.com'],
        ['domain' => ['required', 'usercheck:domain_only,block_blocklisted']]
    );

    expect($validator->passes())->toBeTrue();
});

// Parameter Combination Tests
test('handles conflicting parameters in validator', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => true,
            'mx' => false,
            'blocklisted' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'test@example.com'],
        ['email' => 'usercheck:domain_only,block_disposable,block_no_mx,block_public_domain,block_blocklisted']
    );

    expect($validator->fails())->toBeTrue();
});

test('handles duplicate parameters in validator', function () {
    Http::fake([
        'https://api.usercheck.com/email/*' => Http::response([
            'disposable' => true,
            'public_domain' => false,
            'mx' => true,
        ], 200),
    ]);

    $validator = Validator::make(
        ['email' => 'test@example.com'],
        ['email' => 'usercheck:block_disposable,block_disposable']
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))
        ->toBe(trans('usercheck::validation.usercheck_disposable', ['attribute' => 'email']));
});
