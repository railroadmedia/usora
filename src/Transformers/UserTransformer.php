<?php

namespace Railroad\Usora\Transformers;

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
        ];
    }
}