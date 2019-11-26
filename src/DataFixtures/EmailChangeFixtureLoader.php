<?php

namespace Railroad\Usora\DataFixtures;

use Carbon\Carbon;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Railroad\Usora\Entities\EmailChange;
use Railroad\Usora\Entities\User;

class EmailChangeFixtureLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $user =
            $manager->getRepository(User::class)
                ->find(1);

        $emailChange = new EmailChange();
        $emailChange->setEmail('test_change@test.com');
        $emailChange->setToken('token1');
        $emailChange->setUser($user);
        $emailChange->setCreatedAt(Carbon::now());

        $manager->persist($emailChange);

        $user2 =
            $manager->getRepository(User::class)
                ->find(2);

        $emailChange2 = new EmailChange();
        $emailChange2->setEmail('test_change2@test.com');
        $emailChange2->setToken('token2');
        $emailChange2->setUser($user2);
        $emailChange2->setUpdatedAt(
            Carbon::now()
                ->subYear(1)
        );
        $emailChange2->setCreatedAt(
            Carbon::now()
                ->subYear(1)
        );

        $manager->persist($emailChange2);

        $manager->flush();
        $manager->clear();
    }
}