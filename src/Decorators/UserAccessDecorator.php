<?php

namespace Railroad\Usora\Decorators;

use Illuminate\Database\DatabaseManager;
use Railroad\Permissions\Services\ConfigService;
use Railroad\Resora\Collections\BaseCollection;
use Railroad\Resora\Decorators\DecoratorInterface;

class UserAccessDecorator implements DecoratorInterface
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * @param BaseCollection $users
     * @return BaseCollection
     */
    public function decorate($users)
    {
        $userIds = $users->pluck('id');

        $usersAccesses =
            $this->databaseManager->table(ConfigService::$tableUserAccess)
                ->join(
                    ConfigService::$tableAccess,
                    ConfigService::$tableAccess . '.id',
                    '=',
                    ConfigService::$tableUserAccess . '.access_id'
                )
                ->whereIn('user_id', $userIds)
                ->get();

        foreach ($users as $userIndex => $user) {
            $users[$userIndex]['access'] = [];

            foreach ($usersAccesses as $usersAccessIndex => $usersAccess) {
                if ($usersAccess->user_id == $user['id']) {
                    $users[$userIndex]['access'][] = [
                        'id' => $usersAccess->access_id,
                        'slug' => $usersAccess->slug,
                        'name' => $usersAccess->name,
                        'description' => $usersAccess->description,
                        'brand' => $usersAccess->brand,
                    ];
                }
            }
        }

        return $users;
    }
}