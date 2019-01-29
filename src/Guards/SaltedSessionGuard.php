<?php

namespace Railroad\Usora\Guards;

use Doctrine\ORM\ORMException;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Str;
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

    protected $user;

    /**
     * @return Authenticatable|null
     */
    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get($this->getName());
        $salt = $this->session->get($this->getSaltName());

        if (!is_null($id)) {
            $user = $this->provider->retrieveById($id);
            $this->user = $user;

            if ($user->getSessionSalt() === $salt) {
                return parent::user();
            }
        }
    }

    public function login(AuthenticatableContract $user, $remember = false)
    {
        parent::login($user, $remember);

        if (self::$updateSalt && empty($user->getSessionSalt())) {
            $salt = Str::random(60);

            $this->session->put($this->getSaltName(), $salt);
            $this->provider->updateSessionSalt($user, $salt);
        } else {
            $this->session->put($this->getSaltName(), $user->getSessionSalt());
        }
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

        if (!is_null($this->user)) {
            $this->cycleRememberToken($user);
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