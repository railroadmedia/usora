<?php

namespace Railroad\Usora\Tests\Functional;

use Railroad\Usora\DataFixtures\UserFieldFixtureLoader;
use Railroad\Usora\Entities\UserField;
use Railroad\Usora\Repositories\UserFieldRepository;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Tests\UsoraTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Railroad\Usora\DataFixtures\UserFixtureLoader;
use Railroad\Usora\Entities\User;

class UserFieldDecoratorTest extends UsoraTestCase
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var UserFieldRepository
     */
    protected $userFieldRepository;

    protected function setUp()
    {
        parent::setUp();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute([app(UserFixtureLoader::class), app(UserFieldFixtureLoader::class)]);

        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->userFieldRepository = $this->entityManager->getRepository(UserField::class);
    }

    public function test_decorate_none()
    {

        $response = $this->userRepository->find(2);

        $this->assertEquals([], $response->getFields());
    }

    public function test_decorate_multiple()
    {
        $response = $this->userRepository->find(1);

        $this->assertEquals(3, count($response->getFields()));
    }
}