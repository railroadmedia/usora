<?php

namespace Railroad\Usora\Repositories;

use Doctrine\ORM\EntityRepository;
use Railroad\Usora\Entities\RememberToken;
use Railroad\Usora\Managers\UsoraEntityManager;

/**
 * Class RememberTokenRepository
 *
 * @method RememberToken findOneBy(array $criteria, array $orderBy = null)
 * @method RememberToken[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method RememberToken[] findAll()
 *
 * @package Railroad\Usora\Repositories
 */
class RememberTokenRepository extends EntityRepository
{
    /**
     * RememberTokenRepository constructor.
     *
     * @param UsoraEntityManager $em
     */
    public function __construct(UsoraEntityManager $em)
    {
        parent::__construct($em, $em->getClassMetadata(RememberToken::class));
    }
}
