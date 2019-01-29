<?php

namespace Railroad\Usora\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Managers\UsoraEntityManager;

/**
 * Class UserRepository
 *
 * @package Railroad\Usora\Repositories
 */
class UserRepository extends EntityRepository
{
    /**
     * UserRepository constructor.
     *
     * @param UsoraEntityManager $em
     */
    public function __construct(UsoraEntityManager $em)
    {
        parent::__construct($em, $em->getClassMetadata(User::class));
    }
}