<?php

namespace Railroad\Usora\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Illuminate\Support\Facades\Hash;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Entities\UserField;

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
        $count = 20;
        $interval = 1;

        while ($interval <= $count) {
            $user = new User();
            $user->setEmail('test+' . $interval . '@test.com');
            $user->setDisplayName('testuser' . $interval);
            $user->setPassword(Hash::make('Password' . $interval . '#'));
            $user->setSessionSalt('salt' . $interval);

            $fieldCount = rand(1, 5);
            $fieldInterval = 1;

            while ($fieldInterval <= $fieldCount) {
                $field = new UserField();
                $field->setKey($this->faker->word . '_' . $this->faker->word);
                $field->setValue($this->faker->randomNumber());
                $field->setUser($user);

                $user->getFields()
                    ->add($field);

                $manager->persist($field);

                $fieldInterval++;
            }

            $manager->persist($user);

            $interval++;
        }

        $manager->flush();
        $manager->clear();
    }
}