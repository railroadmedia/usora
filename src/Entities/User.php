<?php

namespace Railroad\Usora\Entities;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\OneToMany(targetEntity="RememberToken", mappedBy="user", orphanRemoval=true)
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @var ArrayCollection
     */
    protected $rememberTokens;

    /**
     * @var string
     */
    protected $currentRememberToken;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $sessionSalt;

    /**
     * @ORM\Column(type="string", length=64)
     * @var string
     */
    protected $displayName;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->rememberTokens = new ArrayCollection();
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
     * @param bool $hash
     */
    public function setPassword($password, $hash = true)
    {
        $this->password = $hash ? Hash::make($password) : $password;
    }

    /**
     * @return ArrayCollection|RememberToken[]
     */
    public function getRememberTokens()
    {
        return $this->rememberTokens;
    }

    /**
     * @param RememberToken $rememberToken
     */
    public function addRememberToken($rememberToken)
    {
        $this->rememberTokens->add($rememberToken);
        $rememberToken->setUser($this);
    }

    /**
     * @param RememberToken $rememberToken
     */
    public function deleteRememberToken($rememberToken)
    {
        $this->rememberTokens->removeElement($rememberToken);
    }

    /**
     * @return string
     */
    public function getRememberToken()
    {
        return $this->currentRememberToken;
    }

    /**
     * @param string $value
     */
    public function setRememberToken($value)
    {
        $this->currentRememberToken = $value;
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