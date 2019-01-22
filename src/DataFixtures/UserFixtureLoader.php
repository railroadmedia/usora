<?php

namespace Railroad\Usora\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use Faker\ORM\Doctrine\Populator;
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
        $populator = new Populator($this->faker, $manager);

        $populator->addEntity(User::class, 3);

        $populator->execute();
    }
}