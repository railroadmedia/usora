<?php

namespace Railroad\Usora\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Notifications\AnonymousNotifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @ORM\Entity(repositoryClass="Railroad\Usora\Repositories\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="usora_users")
 */
class User implements Authenticatable, CanResetPassword, JWTSubject
{
    use TimestampableEntity, \Illuminate\Auth\Passwords\CanResetPassword;

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
     * @ORM\Column(type="string")
     * @var string
     */
    protected $rememberToken = '';

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $sessionSalt = '';

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $displayName;

    /**
     * @ORM\OneToMany(targetEntity="UserField", mappedBy="user", fetch="EAGER")
     */
    private $fields;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getRememberToken(): string
    {
        return $this->rememberToken;
    }

    /**
     * @param string $rememberToken
     */
    public function setRememberToken($rememberToken): void
    {
        $this->rememberToken = $rememberToken;
    }

    /**
     * @return string
     */
    public function getSessionSalt(): string
    {
        return $this->sessionSalt;
    }

    /**
     * @param string $sessionSalt
     */
    public function setSessionSalt(string $sessionSalt): void
    {
        $this->sessionSalt = $sessionSalt;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @return ArrayCollection|UserField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param mixed $fields
     */
    public function setFields($fields): void
    {
        $this->fields = $fields;
    }

    // functions for laravel auth
    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier(): int
    {
        return $this->getId();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword(): string
    {
        return $this->getPassword();
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public function sendPasswordResetNotification($token)
    {
        $class = config('usora.password_reset_notification_class');

        (new AnonymousNotifiable())->route(
            config('usora.password_reset_notification_channel'),
            $this->getEmailForPasswordReset()
        )
            ->notify(new $class($token));

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
                'id' => $this->getId(),
            ],
        ];
    }
}