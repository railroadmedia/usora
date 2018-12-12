<?php

namespace Railroad\Usora\Entities;

use ArrayAccess;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Notifications\AnonymousNotifiable;
use Railroad\Resora\Entities\Entity;
use Railroad\Usora\Services\ConfigService;
use Railroad\Permissions\Services\ConfigService as PermissionConfigService;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Entity implements Authenticatable, ArrayAccess, CanResetPassword, JWTSubject
{
    public function dot()
    {
        $original = $this->getArrayCopy();
        $dotArray = [];

        // fields
        foreach ($this['fields'] ?? [] as $field) {
            $dotArray['fields.' . $field['key']] = $field['value'];
        }

        unset($original['fields']);

        if (empty($dotArray['fields.profile_picture_image_url'])) {
            $dotArray['fields.profile_picture_image_url'] =
                'https://dmmior4id2ysr.cloudfront.net/assets/images/avatar.jpg';
        }

        return array_merge(array_dot($original), $dotArray);
    }

    /**
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * @return mixed|null
     */
    public function getAuthIdentifier()
    {
        return $this[$this->getAuthIdentifierName()];
    }

    /**
     * @return mixed|null|string
     */
    public function getAuthPassword()
    {
        return $this['password'];
    }

    /**
     * @return mixed|null|string
     */
    public function getRememberToken()
    {
        return $this[$this->getRememberTokenName()];
    }

    /**
     * @param string $value
     */
    public function setRememberToken($value)
    {
        $this['remember_token'] = $value;
    }

    /**
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    public function getEmailForPasswordReset()
    {
        return $this['email'];
    }

    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        (new AnonymousNotifiable)
            ->route(ConfigService::$passwordResetNotificationChannel, $this->getEmailForPasswordReset())
            ->notify(new ConfigService::$passwordResetNotificationClass($token));
    }

    public function is($role)
    {
        foreach ($this['permissions']['roles'] as $userRole) {
            if ($userRole == $role) {
                return true;
            }
        }

        return false;
    }

    public function can($ability)
    {
        foreach ($this['permissions']['roles'] as $userRole) {
            foreach(PermissionConfigService::$roleAbilities[$userRole] ?? [] as $roleAbility)
            {
                if ($roleAbility == $ability) {
                    return true;
                }
            }
        }

        foreach ($this['permissions']['abilities'] as $userAbility) {
            if ($userAbility == $ability) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getAuthIdentifier();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user' => [
                'id' => $this['id']
             ]
        ];
    }
}