<?php

namespace Railroad\Usora\Transformers;

use Doctrine\ORM\EntityManager;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Railroad\Doctrine\Serializers\BasicEntitySerializer;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Managers\UsoraEntityManager;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        $entityManager = app()->make(UsoraEntityManager::class);
        $serializer = new BasicEntitySerializer();

        return (new Collection(
            $serializer->serializeToUnderScores(
                $user,
                $entityManager->getClassMetadata(get_class($user))
            )
        ))->except(['password', 'remember_token', 'session_salt'])
            ->toArray();
    }
}