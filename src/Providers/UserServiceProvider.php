<?php

namespace Railroad\Usora\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Repositories\UserRepository;

class UserServiceProvider implements UserProvider
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Hasher
     */
    private $hasher;

    /**
     * UserServiceProvider constructor.
     *
     * @param UserRepository $userRepository
     * @param Hasher $hasher
     */
    public function __construct(UserRepository $userRepository, Hasher $hasher)
    {
        $this->userRepository = $userRepository;
        $this->hasher = $hasher;
    }

    /**
     * @param mixed $identifier
     * @return User
     */
    public function retrieveById($identifier)
    {
        return $this->mapToEntity($this->userRepository->read($identifier));
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
        return $this->userRepository->update(
            $user->getAuthIdentifier(),
            [$user->getRememberTokenName() => $token]
        );
    }

    /**
     * @param Authenticatable $user
     * @param string $salt
     * @return int|null
     */
    public function updateSessionSalt(Authenticatable $user, $salt)
    {
        return $this->userRepository->update(
            $user->getAuthIdentifier(),
            ['session_salt' => $salt]
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

        return $this->mapToEntity($this->userRepository->query()->where($getByAttributes)->first());
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