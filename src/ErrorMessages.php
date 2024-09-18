<?php

namespace UserCheck\Laravel;

class ErrorMessages
{
    public const DEFAULT = 'usercheck';

    public const DISPOSABLE = 'usercheck_disposable';

    public const NO_MX = 'usercheck_no_mx';

    public const PUBLIC_DOMAIN = 'usercheck_public_domain';

    public static function get(string $key, string $attribute): string
    {
        return trans("usercheck::validation.{$key}", ['attribute' => $attribute]);
    }

    public static function forErrorCode(?string $errorCode, string $attribute): string
    {
        $key = match ($errorCode) {
            'disposable' => self::DISPOSABLE,
            'no_mx' => self::NO_MX,
            'public_domain' => self::PUBLIC_DOMAIN,
            default => self::DEFAULT,
        };

        return self::get($key, $attribute);
    }
}
