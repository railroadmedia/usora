<?php

namespace Railroad\Usora\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Services\UserService;

class UserServiceProvider implements UserProvider
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var Hasher
     */
    private $hasher;

    /**
     * UserServiceProvider constructor.
     * @param UserService $userService
     * @param Hasher $hasher
     */
    public function __construct(UserService $userService, Hasher $hasher)
    {
        $this->userService = $userService;
        $this->hasher = $hasher;
    }

    /**
     * @param mixed $identifier
     * @return User
     */
    public function retrieveById($identifier)
    {
        return $this->mapToEntity($this->userService->getById($identifier));
    }

    /**
     * @param mixed $identifier
     * @param string $token
     * @return Authenticatable|null|User
     */
    public function retrieveByToken($identifier, $token)
    {
        $user = $this->retrieveById($identifier);

        if (!$user) {
            return null;
        }

        $rememberToken = $user->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token) ? $user : null;
    }

    /**
     * @param Authenticatable $user
     * @param string $token
     * @return int|null
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        return $this->userService->updateOrCreate(
            [$user->getAuthIdentifierName() => $user->getAuthIdentifier()],
            [$user->getRememberTokenName() => $token]
        );
    }

    /**
     * @param array $credentials
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
                array_key_exists('password', $credentials))) {
            return null;
        }

        $getByAttributes = [];

        foreach ($credentials as $key => $value) {
            if (!Str::contains($key, 'password')) {
                $getByAttributes[$key] = $value;
            }
        }

        return $this->mapToEntity($this->userService->getByCredentials($getByAttributes));
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }

    /**
     * @param $data
     * @return User|null
     */
    private function mapToEntity($data)
    {
        if (empty($data)) {
            return null;
        }

        $user = new User();

        foreach ($data as $name => $value) {
            $user[$name] = $value;
        }

        return $user;
    }
}