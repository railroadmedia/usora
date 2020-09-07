<?php

namespace Railroad\Usora\Repositories;

use Doctrine\ORM\EntityRepository;
use Railroad\Usora\Entities\UserTopics;
use Railroad\Usora\Managers\UsoraEntityManager;

/**
 * Class UserTopicsRepository
 *
 * @method UserTopics findOneBy(array $criteria, array $orderBy = null)
 * @method UserTopics[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method UserTopics[] findAll()
 *
 * @package Railroad\Usora\Repositories
 */
class UserTopicsRepository extends EntityRepository
{
    /**
     * UserFirebaseTokensRepository constructor.
     *
     * @param UsoraEntityManager $em
     */
    public function __construct(UsoraEntityManager $em)
    {
        parent::__construct($em, $em->getClassMetadata(UserTopics::class));
    }

}
