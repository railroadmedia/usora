<?php

namespace Railroad\Usora\Decorators;

use Railroad\Resora\Collections\BaseCollection;
use Railroad\Resora\Decorators\DecoratorInterface;
use Railroad\Resora\Entities\Entity;
use Railroad\Usora\Entities\User;

class UserEntityDecorator implements DecoratorInterface
{
    /**
     * @param $results Entity[]|BaseCollection
     * @return BaseCollection|Entity[]
     */
    public function decorate($results)
    {
        foreach ($results as $resultsIndex => $result) {
            if (!($result instanceof User)) {
                $results[$resultsIndex] = new User($result->getArrayCopy());
            }
        }

        return $results;
    }
}