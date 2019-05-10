<?php

namespace Railroad\Usora\Events;

use Railroad\Usora\Entities\User;

class UserUpdated
{
    /**
     * @var User
     */
    private $newUser;

    /**
     * @var User
     */
    private $oldUser;

    /**
     * Create a new event instance.
     *
     * @param User $newUser
     * @param User $oldUser
     */
    public function __construct(User $newUser, User $oldUser)
    {
        $this->newUser = $newUser;
        $this->oldUser = $oldUser;
    }

    /**
     * @return User
     */
    public function getNewUser(): User
    {
        return $this->newUser;
    }

    /**
     * @return User
     */
    public function getOldUser(): User
    {
        return $this->oldUser;
    }
}
