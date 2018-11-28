<?php

namespace Railroad\Usora\Entities;

use DateTime;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;

/**
 * @Entity @Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */
class User
{
    use Timestamps;

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $email;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $password;

    /**
     * @Column(type="string",nullable=true)
     * @var string
     */
    protected $rememberToken;

    /**
     * @Column(type="string",nullable=true)
     * @var string
     */
    protected $sessionSalt;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $displayName;

    /**
     * User constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setCreatedAt(new DateTime());

        if ($this->getUpdatedAt() == null) {
            $this->setUpdatedAt(new DateTime());
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * @throws \Exception
     */
    public function updateModifiedDatetime()
    {
        $this->setUpdatedAt(new DateTime());
    }

    /**
     * @return string
     */
    public function getEmail()
    : string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    : void {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword()
    : string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    : void {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getRememberToken()
    : string
    {
        return $this->rememberToken;
    }

    /**
     * @param string $rememberToken
     */
    public function setRememberToken(string $rememberToken)
    : void {
        $this->rememberToken = $rememberToken;
    }

    /**
     * @return string
     */
    public function getSessionSalt()
    : string
    {
        return $this->sessionSalt;
    }

    /**
     * @param string $sessionSalt
     */
    public function setSessionSalt(string $sessionSalt)
    : void {
        $this->sessionSalt = $sessionSalt;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    : string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName(string $displayName)
    : void {
        $this->displayName = $displayName;
    }

    public function getId()
    {
        return $this->id;
    }
}