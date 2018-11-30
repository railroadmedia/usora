<?php

namespace Railroad\Usora\DataFixtures;

use Carbon\Carbon;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Illuminate\Support\Facades\Hash;
use Railroad\Usora\Entities\User;

class UserFixtureLoader implements FixtureInterface
{
    /**
     * @var Generator
     */
    private $faker;

    /**
     * UserFixtureLoader constructor.
     */
    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function load(ObjectManager $manager)
    {
        $count = 2;

        while ($count > 0) {
            $user = new User();
            $user->setEmail('test+' . $count . '@test.com');
            $user->setDisplayName('testuser' . $count);
            $user->setPassword(Hash::make('Password' . $count . '#'));

            $manager->persist($user);

            $count--;
        }

        $manager->flush();
        $manager->clear();
    }
}