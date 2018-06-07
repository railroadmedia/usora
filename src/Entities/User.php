<?php

namespace Railroad\Usora\Entities;

use ArrayAccess;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Notifications\AnonymousNotifiable;
use Railroad\Resora\Entities\Entity;
use Railroad\Usora\Services\ConfigService;

class User extends Entity implements Authenticatable, ArrayAccess, CanResetPassword
{
    /**
     * @param $accessSlug
     * @return bool
     */
    public function can($accessSlug)
    {
        foreach (array_column($this['access'], 'slug') as $userAccessSlug) {
            if ($userAccessSlug === $accessSlug) {
                return true;
            }
        }

        return false;
    }

    public function dot()
    {
        $original = $this->getArrayCopy();
        $dotArray = [];

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
}