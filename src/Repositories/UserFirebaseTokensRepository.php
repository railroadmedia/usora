<?php

namespace Railroad\Usora\Repositories;

use Doctrine\ORM\EntityRepository;
use Railroad\Usora\Entities\UserFirebaseTokens;
use Railroad\Usora\Managers\UsoraEntityManager;

/**
 * Class UserFirebaseTokensRepository
 *
 * @method UserFirebaseTokens findOneBy(array $criteria, array $orderBy = null)
 * @method UserFirebaseTokens[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method UserFirebaseTokens[] findAll()
 *
 * @package Railroad\Usora\Repositories
 */
class UserFirebaseTokensRepository extends EntityRepository
{
    /**
     * UserFirebaseTokensRepository constructor.
     *
     * @param UsoraEntityManager $em
     */
    public function __construct(UsoraEntityManager $em)
    {
        parent::__construct($em, $em->getClassMetadata(UserFirebaseTokens::class));
    }

}
