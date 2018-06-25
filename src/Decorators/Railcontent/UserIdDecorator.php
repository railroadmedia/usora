<?php

namespace Railroad\Usora\Decorators\Railcontent;

use Railroad\Usora\Repositories\UserRepository;

class UserIdDecorator
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

        $users = $this->userRepository->query()
            ->select(['id', 'display_name', 'created_at'])
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        foreach ($results as $resultIndex => $result) {
            if (!empty($users[$result['user_id']])) {
                $results[$resultIndex]['user'] = $users[$result['user_id']]->dot();
            }
        }

        return $results;
    }
}