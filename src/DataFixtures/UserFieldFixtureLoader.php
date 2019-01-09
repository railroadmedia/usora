<?php

namespace Railroad\Usora\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Entities\UserField;

class UserFieldFixtureLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $count = 3;
        $interval = 1;

        while ($interval <= $count) {
            $user =
                $manager->getRepository(User::class)
                    ->find(1);
            $userField = new UserField();
            $userField->setKey('key+' . $interval);
            $userField->setValue('value ' . $interval);
            $userField->setUser($user);

            $manager->persist($userField);

            $interval++;
        }

        $manager->flush();
        $manager->clear();
    }
}