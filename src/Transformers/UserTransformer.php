<?php

namespace Railroad\Usora\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Railroad\Usora\Entities\User;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'display_name' => $user->getDisplayName(),
            'created_at' => Carbon::instance($user->getCreatedAt())->toDateTimeString(),
            'updated_at' => Carbon::instance($user->getUpdatedAt())->toDateTimeString(),
        ];
    }
}