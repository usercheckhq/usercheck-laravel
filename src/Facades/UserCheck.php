<?php

namespace UserCheck\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class UserCheck extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'usercheck';
    }
}
