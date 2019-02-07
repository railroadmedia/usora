<?php

namespace Railroad\Usora\Entities;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Railroad\Usora\Repositories\RememberTokenRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="usora_remember_tokens")
 */
class RememberToken
{
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Railroad\Usora\Entities\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $token;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $deviceInformation;

    /**
     * @var Carbon
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var Carbon
     * @ORM\Column(type="datetime")
     */
    protected $expiresAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getDeviceInformation()
    {
        return $this->deviceInformation;
    }

    /**
     * @param string $deviceInformation
     */
    public function setDeviceInformation($deviceInformation)
    {
        $this->deviceInformation = $deviceInformation;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param Carbon $createdAt
     */
    public function setCreatedAt(Carbon $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return Carbon
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param Carbon $expiresAt
     */
    public function setExpiresAt(Carbon $expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }
}