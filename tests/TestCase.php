<?php

namespace UserCheck\Laravel\Tests;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use UserCheck\Laravel\UserCheckProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [UserCheckProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('usercheck.api_key', 'test_api_key');

        $app['path.lang'] = __DIR__.'/../resources/lang';
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }
}
