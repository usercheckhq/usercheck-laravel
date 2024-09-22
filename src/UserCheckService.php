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
        $apiKey = config('usercheck.api_key');
        if (! is_string($apiKey)) {
            throw new InvalidArgumentException('UserCheck API key is not set.');
        }
        $this->apiKey = $apiKey;
    }

    /**
     * @return array<string, bool|string|null>
     */
    public function validateEmail(string $email, bool $blockDisposable = false, bool $blockNoMx = false, bool $blockPublicDomain = false): array
    {
        return $this->validate('email', $email, $blockNoMx, $blockPublicDomain, $blockDisposable);
    }

    /**
     * @return array<string, bool|string|null>
     */
    public function validateDomain(string $domain, bool $blockDisposable = false, bool $blockNoMx = false, bool $blockPublicDomain = false): array
    {
        return $this->validate('domain', $domain, $blockNoMx, $blockPublicDomain, $blockDisposable);
    }

    /**
     * @return array<string, bool|string|null>
     */
    protected function validate(string $endpoint, string $value, bool $blockNoMx, bool $blockPublicDomain, bool $blockDisposable): array
    {
        $response = Http::withToken($this->apiKey)
            ->withHeader('User-Agent', 'UserCheck-Laravel/0.0.1 (https://github.com/usercheckhq/laravel)')
            ->get("https://api.usercheck.com/{$endpoint}/".urlencode($value));

        if ($response->status() === 400) {
            return [
                'is_valid' => false,
                'error_code' => 'usercheck',
            ];
        }

        if (! $response->successful()) {
            throw new ApiRequestException("Unable to verify {$endpoint}: ".$response->body());
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new ApiRequestException('Invalid response format from UserCheck API');
        }

        $isValid = $this->checkValidity($data, $blockNoMx, $blockPublicDomain, $blockDisposable);

        return [
            'is_valid' => $isValid,
            'error_code' => $this->getErrorCode($data, $blockNoMx, $blockPublicDomain, $blockDisposable),
        ];
    }

    /**
     * @param  array<string, bool>  $data
     */
    protected function checkValidity(array $data, bool $blockNoMx, bool $blockPublicDomain, bool $blockDisposable): bool
    {
        if ($blockDisposable && ($data['disposable'] ?? false)) {
            return false;
        }
        if ($blockPublicDomain && ($data['public_domain'] ?? false)) {
            return false;
        }
        if ($blockNoMx && ! ($data['mx'] ?? true)) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, bool>  $data
     */
    protected function getErrorCode(array $data, bool $blockNoMx, bool $blockPublicDomain, bool $blockDisposable): ?string
    {
        if ($blockDisposable && ($data['disposable'] ?? false)) {
            return 'disposable';
        }
        if ($blockPublicDomain && ($data['public_domain'] ?? false)) {
            return 'public_domain';
        }
        if ($blockNoMx && ! ($data['mx'] ?? true)) {
            return 'no_mx';
        }

        return null;
    }
}
