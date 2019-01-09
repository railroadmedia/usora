<?php

namespace Railroad\Usora\Faker;

use Faker\Generator;
use Illuminate\Support\Facades\Hash;

class Faker extends Generator
{
    public function user(array $override = [])
    {
        return array_merge(
            [
                'email' => $this->email,
                'password' => Hash::make($this->word),
                'remember_token' => Hash::make($this->word),
                'session_salt' => Hash::make($this->word),
                'display_name' => $this->userName,
                'created_at' => $this->dateTimeThisCentury(),
                'updated_at' => $this->dateTimeThisCentury(),
            ],
            $override
        );
    }

    public function userField(array $override = [])
    {
        return array_merge(
            [
                'user_id' => rand(),
                'key' => $this->word,
                'value' => $this->word,
                'index' => Hash::make($this->word),
                'created_at' => $this->dateTimeThisCentury(),
                'updated_at' => $this->dateTimeThisCentury(),
            ],
            $override
        );
    }
}