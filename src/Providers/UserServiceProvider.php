<?php

namespace Railroad\Usora\Providers;

use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Managers\UsoraEntityManager;

class UserServiceProvider implements UserProvider
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Hasher
     */
    private $hasher;

    /**
     * UserServiceProvider constructor.
     *
     * @param EntityManager $entityManager
     * @param Hasher $hasher
     */
    public function __construct(UsoraEntityManager $entityManager, Hasher $hasher)
    {
        $this->entityManager = $entityManager;
        $this->hasher = $hasher;
    }

    /**
     * @param mixed $identifier
     * @return User
     */
    public function retrieveById($identifier)
    {
        return $this->entityManager->getRepository(User::class)
            ->find($identifier);
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
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user =
            $this->entityManager->getRepository(User::class)
                ->find($user->getAuthIdentifier());

        if (!is_null($user)) {
            $user->setRememberToken($token);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @param Authenticatable $user
     * @param string $salt
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateSessionSalt(Authenticatable $user, $salt)
    {
        $user =
            $this->entityManager->getRepository(User::class)
                ->find($user->getAuthIdentifier());

        if (!is_null($user)) {
            $user->setSessionSalt($salt);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * @param array $credentials
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) || (count($credentials) === 1 && array_key_exists('password', $credentials))) {
            return null;
        }

        $getByAttributes = [];

        foreach ($credentials as $key => $value) {
            if (!Str::contains($key, 'password')) {
                $getByAttributes[$key] = $value;
            }
        }

        return $this->entityManager->getRepository(User::class)
            ->findOneBy($getByAttributes);
    }

    /**
     * @param Authenticatable $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }
}