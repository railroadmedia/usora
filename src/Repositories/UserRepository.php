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
 * @method User find($id, $lockMode = null, $lockVersion = null)
 * @method User findOneBy(array $criteria, array $orderBy = null)
 * @method User[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method User[] findAll()
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