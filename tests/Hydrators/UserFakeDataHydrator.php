<?php

namespace Railroad\Usora\Tests\Hydrators;

use Railroad\Doctrine\Hydrators\FakeDataHydrator;
use Railroad\Usora\Entities\User;

class UserFakeDataHydrator extends FakeDataHydrator
{
    public function fill(&$entity, $customColumnFormatters = [])
    {
        /**
         * @var $defaultEntity User
         */
        $defaultEntity = parent::fill($entity, $customColumnFormatters);

        $defaultEntity->setDrumsPlayingSinceYear(rand(1900, 2019));
        $defaultEntity->setPianoPlayingSinceYear(rand(1900, 2019));
        $defaultEntity->setGuitarPlayingSinceYear(rand(1900, 2019));

        return $defaultEntity;
    }
}