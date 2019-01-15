<?php

namespace Railroad\Usora\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Railroad\Usora\Entities\UserField;

class UserFieldTransformer extends TransformerAbstract
{
    public function transform(UserField $userField)
    {
        return [
            'id' => $userField->getId(),
            'key' => $userField->getKey(),
            'value' => $userField->getValue(),
            'created_at' => Carbon::instance($userField->getCreatedAt())->toDateTimeString(),
            'updated_at' => Carbon::instance($userField->getUpdatedAt())->toDateTimeString(),
        ];
    }
}