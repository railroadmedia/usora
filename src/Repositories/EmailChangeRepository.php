<?php

namespace Railroad\Usora\Repositories;

use Doctrine\ORM\EntityRepository;
use Railroad\Usora\Entities\User;

/**
 * Class EmailChangeRepository
 *
 * @package Railroad\Usora\Repositories
 */
class EmailChangeRepository extends EntityRepository
{
    public function updateOrCreateForUser($userId, $newEmail, $newToken)
    {
        $existing = $this->getEntityManager()->getRepository(User::class)->findOneBy(['user_id' => $userId]);

        if (!is_null($existing)) {
//            $existing->setEmail()
        }
    }
}
