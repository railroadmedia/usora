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

        $defaultEntity->setDisplayName($this->faker->userName);

        $defaultEntity->setDrumsPlayingSinceYear(rand(1900, 2019));
        $defaultEntity->setPianoPlayingSinceYear(rand(1900, 2019));
        $defaultEntity->setGuitarPlayingSinceYear(rand(1900, 2019));
        $defaultEntity->setNotificationsSummaryFrequencyMinutes(rand(0, 5000));
        $defaultEntity->setPhoneNumber(rand(10000000000, 99999999999));

        return $defaultEntity;
    }
}