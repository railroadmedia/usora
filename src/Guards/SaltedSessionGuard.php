<?php

namespace Railroad\Usora\Guards;

use Doctrine\ORM\ORMException;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Str;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Providers\UserServiceProvider;

class SaltedSessionGuard extends SessionGuard
{
    /**
     * @var UserServiceProvider
     */
    public $provider;

    /**
     * @var bool
     */
    public static $updateSalt = true;

    /**
     * @var Authenticatable|User
     */
    protected $user;

    public function nullCurrentUser()
    {
        $this->user = null;
    }

    /**
     * @return Authenticatable|null|User
     */
    public function user()
    {
        if ($this->loggedOut) {
            return null;
        }

        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get($this->getName());
        $salt = $this->session->get($this->getSaltName());

        if (!is_null($id)) {
            $user = $this->provider->retrieveById($id);
            $this->user = $user;

            if ($user->getSessionSalt() === $salt) {
                return $this->user;
            }
        }

        $recaller = $this->recaller();

        if (is_null($this->user) && !is_null($recaller)) {
            $this->user = $this->userFromRecaller($recaller);

            if ($this->user) {
                $this->updateSession($this->user->getAuthIdentifier());

                $this->fireLoginEvent($this->user, true);

                return $this->user;
            }
        }

        return null;
    }

    /**
     * @param AuthenticatableContract $user
     * @param bool $remember
     * @throws ORMException
     */
    public function login(AuthenticatableContract $user, $remember = false)
    {
        $this->updateSession($user->getAuthIdentifier());

        if ($remember) {
            $this->createAndQueueRememberToken($user);

            $this->getCookieJar()
                ->queue(
                    $this->createRecaller(
                        $user->getAuthIdentifier() . '|' . $user->getRememberToken() . '|' . $user->getAuthPassword()
                    )
                );
        }

        if (self::$updateSalt && empty($user->getSessionSalt())) {
            $salt = Str::random(60);

            $this->session->put($this->getSaltName(), $salt);
            $this->provider->updateSessionSalt($user, $salt);
        } else {
            $this->session->put($this->getSaltName(), $user->getSessionSalt());
        }

        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);
    }

    /**
     * Create a new "remember me" token for the user if one doesn't already exist.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|User $user
     * @return void
     * @throws ORMException
     */
    protected function createAndQueueRememberToken(AuthenticatableContract $user)
    {
        $this->provider->updateRememberToken($user, Str::random(60));
    }

    /**
     * Copied from laravel SessionGuard
     *
     * @return void
     * @throws ORMException
     */
    public function logout()
    {
        $user = $this->user();

        if (empty($user)) {
            $this->user = null;

            $this->loggedOut = true;

            return;
        }

        $this->provider->updateSessionSalt($user, '');

        $this->clearUserDataFromStorage();

        if (!is_null($this->user) && !empty($this->recaller())) {
            $recaller = $this->recaller();

            $this->provider->deleteRememberToken($recaller->token(), $user->getAuthIdentifier());
        }

        if (isset($this->events)) {
            $this->events->dispatch(new Logout($user));
        }

        $this->user = null;

        $this->loggedOut = true;
    }

    protected function getSaltName()
    {
        return 'login_salt_' . $this->name . '_' . sha1(static::class);
    }
}