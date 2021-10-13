<?php

namespace Railroad\Usora\Events;

use Railroad\Usora\Entities\User;

class MobileAppLogin
{
    /**
     * @var User
     */
    private $user;

    private $firebaseToken;

    private $platform;

    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(User $user, $firebaseToken, $platform)
    {
        $this->user = $user;
        $this->firebaseToken = $firebaseToken;
        $this->platform = $platform;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getFirebaseToken(): string
    {
        return $this->firebaseToken;
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }
}
