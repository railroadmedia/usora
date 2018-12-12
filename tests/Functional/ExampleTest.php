<?php

namespace Railroad\Usora\Tests\Functional;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Tests\UsoraTestCase;

class ExampleTest extends UsoraTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function test_example()
    {
        $userId = 1;

        $response = $this->call('GET', '/authenticate/token',
            ['uid' => $userId, 'v' => rand()]);

        $this->assertEmpty($this->app->make('auth')->guard()->id());

        $this->assertTrue(true);
    }
}