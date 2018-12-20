<?php

namespace Railroad\Usora\Entities;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="Railroad\Usora\Repositories\UserFieldRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="usora_user_fields")
 * @ExclusionPolicy("none")
 *
 */
class UserField
{
    use TimestampableEntity;

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Railroad\Usora\Entities\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Exclude
     *
     */
    private $user;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $key;

    /**
     * @ORM\Column(type="text")
     * @var text
     */
    protected $value;

    /**
     * @return int
     */
    public function getId()
    : int
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
    : void {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getKey()
    : string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key)
    : void {
        $this->key = $key;
    }

    /**
     * @return text
     */
    public function getValue()
    : string
    {
        return $this->value;
    }

    /**
     * @param text $value
     */
    public function setValue($value)
    : void {
        $this->value = $value;
    }

}