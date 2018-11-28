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
        $user = new User();
        $user->setEmail('test+1@test.com');
        $user->setDisplayName('testuser1');
        $user->setPassword(Hash::make('Password1#'));

        $manager->persist($user);
        $manager->flush();

        $manager->clear();

        $user2 = $manager->find(User::class, 1);

        dd($user2);
    }
}