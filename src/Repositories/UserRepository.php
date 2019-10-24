<?php

namespace Railroad\Usora\Repositories;

use Doctrine\ORM\EntityRepository;
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

    /**
     * @param array $ids
     * @param bool $keyById
     * @return User[]|array
     */
    public function findByIds(array $ids, $keyById = true)
    {
        $qb = $this->createQueryBuilder('user');

        $users = $qb->where(
            $qb->expr()
                ->in('user.id', ':userIds')
        )
            ->setParameter('userIds', $ids)
            ->getQuery()
            ->getResult();

        $usersKeyedById = [];

        if ($keyById) {
            foreach ($users as $user) {
                $usersKeyedById[$user->getId()] = $user;
            }

            return $usersKeyedById;
        }

        return $users;
    }
}