<?php

namespace Railroad\Usora\Decorators;

use Railroad\Resora\Collections\BaseCollection;
use Railroad\Resora\Decorators\DecoratorInterface;
use Railroad\Usora\Repositories\UserFieldRepository;

class UserFieldDecorator implements DecoratorInterface
{
    /**
     * @var UserFieldRepository
     */
    private $userFieldRepository;

    /**
     * UserFieldDecorator constructor.
     *
     * @param UserFieldRepository $userFieldRepository
     */
    public function __construct(UserFieldRepository $userFieldRepository)
    {
        $this->userFieldRepository = $userFieldRepository;
    }

    /**
     * @param BaseCollection $users
     * @return BaseCollection
     */
    public function decorate($users)
    {
        $userIds = $users->pluck('id');

        $usersFields = $this->userFieldRepository->query()->whereIn('user_id', $userIds)->get();

        foreach ($users as $userIndex => $user) {
            $users[$userIndex]['fields'] = [];

            foreach ($usersFields as $usersFieldIndex => $usersField) {
                if ($usersField['user_id'] == $user['id']) {
                    $users[$userIndex]['fields'][] = $usersField;
                }
            }
        }

        return $users;
    }
}