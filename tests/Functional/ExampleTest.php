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
        $userId = $this->databaseManager->table(ConfigService::$tableUsers)
            ->insertGetId([
                'email' => $this->faker->email,
                'password' => $this->hasher->make($this->faker->word),
                'remember_token' => str_random(60),
                'display_name' => $this->faker->words(4, true),
                'created_at' => time(),
                'updated_at' => time(),
            ]);

        $this->assertTrue(true);
    }
}