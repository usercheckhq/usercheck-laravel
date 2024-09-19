<?php

namespace UserCheck\Laravel;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use UserCheck\Laravel\Exceptions\ApiRequestException;

class UserCheckService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('usercheck.api_key') ?? throw new InvalidArgumentException('UserCheck API key is not set.');
    }

    public function validateEmail(string $email, bool $blockDisposable = false, bool $blockNoMx = false, bool $blockPublicDomain = false): array
    {
        return $this->validate('email', $email, $blockNoMx, $blockPublicDomain, $blockDisposable);
    }

    public function validateDomain(string $domain, bool $blockDisposable = false, bool $blockNoMx = false, bool $blockPublicDomain = false): array
    {
        return $this->validate('domain', $domain, $blockNoMx, $blockPublicDomain, $blockDisposable);
    }

    protected function validate(string $endpoint, string $value, bool $blockNoMx, bool $blockPublicDomain, bool $blockDisposable): array
    {
        $response = Http::withToken($this->apiKey)
            ->withHeader('User-Agent', 'UserCheck-Laravel/0.0.1 (https://github.com/usercheckhq/laravel)')
            ->get("https://api.usercheck.com/{$endpoint}/".urlencode($value));

        if (! $response->successful()) {
            throw new ApiRequestException("Unable to verify {$endpoint}: ".$response->body());
        }

        $data = $response->json();
        $isValid = $this->checkValidity($data, $blockNoMx, $blockPublicDomain, $blockDisposable);

        return [
            'is_valid' => $isValid,
            'error_code' => $this->getErrorCode($data, $blockNoMx, $blockPublicDomain, $blockDisposable),
        ];
    }

    protected function checkValidity(array $data, bool $blockNoMx, bool $blockPublicDomain, bool $blockDisposable): bool
    {
        if ($blockDisposable && $data['disposable']) {
            return false;
        }
        if ($blockPublicDomain && $data['public_domain']) {
            return false;
        }
        if ($blockNoMx && ! $data['mx']) {
            return false;
        }

        return true;
    }

    protected function getErrorCode(array $data, bool $blockNoMx, bool $blockPublicDomain, bool $blockDisposable): ?string
    {
        if ($blockDisposable && $data['disposable']) {
            return 'disposable';
        }
        if ($blockPublicDomain && $data['public_domain']) {
            return 'public_domain';
        }
        if ($blockNoMx && ! $data['mx']) {
            return 'no_mx';
        }

        return null;
    }
}
