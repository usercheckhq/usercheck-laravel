<?php

namespace UserCheck\Laravel\Rules;

use Illuminate\Contracts\Validation\Rule;
use UserCheck\Laravel\ErrorMessages;
use UserCheck\Laravel\UserCheckService;
use UserCheck\Laravel\Exceptions\ApiRequestException;

class UserCheckRule implements Rule
{
    protected string $message = '';

    protected UserCheckService $service;

    protected array $parameters;

    public function __construct(UserCheckService $service, array $parameters = [])
    {
        $this->service = $service;
        $this->parameters = $parameters;
    }

    public function passes($attribute, $value): bool
    {
        $domainOnly = in_array('domain_only', $this->parameters);
        $blockDisposable = in_array('block_disposable', $this->parameters);
        $blockNoMx = in_array('block_no_mx', $this->parameters);
        $blockPublicDomain = in_array('block_public_domain', $this->parameters);

        try {
            $result = $domainOnly
                ? $this->service->validateDomain($value, $blockDisposable, $blockNoMx, $blockPublicDomain)
                : $this->service->validateEmail($value, $blockDisposable, $blockNoMx, $blockPublicDomain);

            if (! $result['is_valid']) {
                $this->message = ErrorMessages::forErrorCode($result['error_code'], $attribute);

                return false;
            }

            return true;
        } catch (ApiRequestException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->message = ErrorMessages::get('validation_failed', $attribute).': '.$e->getMessage();

            return false;
        }
    }

    public function message(): string
    {
        return $this->message ?: ErrorMessages::get(ErrorMessages::DEFAULT, 'attribute');
    }
}
