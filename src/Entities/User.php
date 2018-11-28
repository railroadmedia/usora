<?php

namespace Railroad\Usora\Entities;

use DateTime;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity ORM\@Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */
class User
{
    use Timestamps;

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
     * @ORM\Column(type="string",nullable=true)
     * @var string
     */
    protected $rememberToken;

    /**
     * @ORM\Column(type="string",nullable=true)
     * @var string
     */
    protected $sessionSalt;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $displayName;

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