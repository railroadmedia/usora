<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\Entities\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Tests\UsoraTestCase;

class UserEntityDecoratorTest extends UsoraTestCase
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    protected function setUp()
    {
        parent::setUp();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class)]);
    }

    public function test_decorate()
    {
        $user = $this->entityManager->getRepository(User::class)->find(1);

        $this->assertInstanceOf(User::class, $user);
    }
}