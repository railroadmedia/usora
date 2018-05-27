<?php

namespace Railroad\Usora\Decorators\Railcontent;

use Railroad\Railcontent\Decorators\DecoratorInterface;
use Railroad\Resora\Decorators\Decorator;
use Railroad\Usora\Repositories\UserRepository;

class UserIdDecorator implements DecoratorInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserIdDecorator constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param $results
     * @return mixed
     */
    public function decorate($results)
    {
        $userIds = [];

        foreach ($results as $result) {
            $userIds[] = $result['user_id'];
        }

        $users = $this->userRepository->query()->whereIn('id', $userIds)->get()->keyBy('id');

        foreach ($results as $resultIndex => $result) {
            $results[$resultIndex]['user'] = $users[$result['user_id']]->dot();
        }

        return $results;
    }
}