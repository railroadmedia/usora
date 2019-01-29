<?php

namespace Railroad\Usora\Repositories;

use Doctrine\ORM\EntityRepository;
use Railroad\Usora\Entities\EmailChange;
use Railroad\Usora\Managers\UsoraEntityManager;

/**
 * Class EmailChangeRepository
 *
 * @package Railroad\Usora\Repositories
 */
class EmailChangeRepository extends EntityRepository
{
    /**
     * UserRepository constructor.
     *
     * @param UsoraEntityManager $em
     */
    public function __construct(UsoraEntityManager $em)
    {
        parent::__construct($em, $em->getClassMetadata(EmailChange::class));
    }
}
