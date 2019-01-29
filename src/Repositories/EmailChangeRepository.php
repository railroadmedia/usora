<?php

namespace Railroad\Usora\Repositories;

use Doctrine\ORM\EntityRepository;
use Railroad\Usora\Entities\EmailChange;
use Railroad\Usora\Managers\UsoraEntityManager;

/**
 * Class EmailChangeRepository
 *
 * @method EmailChange findOneBy(array $criteria, array $orderBy = null)
 * @method EmailChange[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method EmailChange[] findAll()
 *
 * @package Railroad\Usora\Repositories
 */
class EmailChangeRepository extends EntityRepository
{
    /**
     * EmailChangeRepository constructor.
     *
     * @param UsoraEntityManager $em
     */
    public function __construct(UsoraEntityManager $em)
    {
        parent::__construct($em, $em->getClassMetadata(EmailChange::class));
    }
}
