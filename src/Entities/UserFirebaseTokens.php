<?php

namespace Railroad\Usora\Entities;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Railroad\Usora\Repositories\UserFirebaseTokensRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="usora_user_firebase_tokens")
 */
class UserFirebaseTokens
{
    const TYPE_IOS = 'ios';
    const TYPE_ANDROID = 'android';
    const TYPE_WEB = 'web';

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
    protected $type;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $token;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $brand;

    /**
     * @var Carbon
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

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
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
}