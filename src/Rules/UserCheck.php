<?php

namespace UserCheck\Laravel\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use UserCheck\Laravel\ErrorMessages;
use UserCheck\Laravel\Exceptions\ApiRequestException;
use UserCheck\Laravel\UserCheckService;

class UserCheck implements ValidationRule
{
    protected UserCheckService $service;

    /** @var array<string> */
    protected array $parameters;

    /**
     * @param  array<string>  $parameters
     */
    public function __construct(UserCheckService $service, array $parameters = [])
    {
        $this->service = $service;
        $this->parameters = $parameters;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $domainOnly = in_array('domain_only', $this->parameters);
        $blockDisposable = in_array('block_disposable', $this->parameters);
        $blockNoMx = in_array('block_no_mx', $this->parameters);
        $blockPublicDomain = in_array('block_public_domain', $this->parameters);
        $blockBlocklisted = in_array('block_blocklisted', $this->parameters);
        $blockRelayDomain = in_array('block_relay_domain', $this->parameters);

        if (! is_string($value)) {
            $fail(ErrorMessages::get('usercheck', $attribute));

            return;
        }

        try {
            $result = $domainOnly
                ? $this->service->validateDomain($value, $blockDisposable, $blockNoMx, $blockPublicDomain, $blockBlocklisted, $blockRelayDomain)
                : $this->service->validateEmail($value, $blockDisposable, $blockNoMx, $blockPublicDomain, $blockBlocklisted, $blockRelayDomain);

            if (! $result['is_valid']) {
                $errorCode = $result['error_code'] ?? null;
                $fail(ErrorMessages::forErrorCode($errorCode !== false ? (string) $errorCode : null, $attribute));
            }
        } catch (ApiRequestException $e) {
            throw $e;
        } catch (\Exception $e) {
            $fail(ErrorMessages::get('validation_failed', $attribute).': '.$e->getMessage());
        }
    }
}
