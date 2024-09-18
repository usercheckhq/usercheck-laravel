<?php

namespace UserCheck\Laravel;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use UserCheck\Laravel\Rules\UserCheckRule;

class UserCheckProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/usercheck.php' => config_path('usercheck.php'),
        ], 'config');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'usercheck');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/usercheck'),
        ], 'lang');

        Validator::extend('usercheck', function ($attribute, $value, $parameters, $validator) {
            $rule = new UserCheckRule($this->app->make(UserCheckService::class), $parameters);
            $passes = $rule->passes($attribute, $value);
            if (! $passes) {
                $validator->setCustomMessages([$attribute => $rule->message()]);
            }

            return $passes;
        });

        Validator::replacer('usercheck', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, $message);
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/usercheck.php', 'usercheck');

        $this->app->singleton(UserCheckService::class);

        $this->app->alias(UserCheckService::class, 'usercheck');
    }
}
