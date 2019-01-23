<?php

namespace Railroad\Usora\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Hash;
use Railroad\Usora\Entities\Traits\BasicUserInformationProperties;
use Railroad\Usora\Entities\Traits\DrumsUserProperties;
use Railroad\Usora\Entities\Traits\GuitarUserProperties;
use Railroad\Usora\Entities\Traits\LaravelAuthUserProperties;
use Railroad\Usora\Entities\Traits\PianoUserProperties;
use Railroad\Usora\Entities\Traits\UserNotificationSettingsProperties;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @ORM\Entity(repositoryClass="Railroad\Usora\Repositories\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="usora_users")
 */
class User implements Authenticatable, CanResetPassword, JWTSubject
{
    use TimestampableEntity;

    use BasicUserInformationProperties;
    use DrumsUserProperties;
    use GuitarUserProperties;
    use PianoUserProperties;
    use LaravelAuthUserProperties;
    use UserNotificationSettingsProperties;

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $rememberToken;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $sessionSalt;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $displayName;

    /**
     * User constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Hash::make($password);
    }

    /**
     * @return string
     */
    public function getRememberToken()
    {
        return $this->rememberToken;
    }

    /**
     * @param string $rememberToken
     */
    public function setRememberToken($rememberToken)
    {
        $this->rememberToken = $rememberToken;
    }

    /**
     * @return string
     */
    public function getSessionSalt()
    {
        return $this->sessionSalt;
    }

    /**
     * @param string $sessionSalt
     */
    public function setSessionSalt($sessionSalt)
    {
        $this->sessionSalt = $sessionSalt;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }
}