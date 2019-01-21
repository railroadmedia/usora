<?php

namespace Railroad\Usora\Entities;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Faker\Generator;
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
        $this->password = $password;
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

    /**
     * @param Generator $faker
     * @param string $testDataIdKey
     */
    public function fillWithFakeData(Generator $faker, $testDataIdKey = '')
    {
        $this->setEmail('test+' . $testDataIdKey . '@test.com');
        $this->setDisplayName('testuser' . $testDataIdKey);
        $this->setPassword(Hash::make('Password' . $testDataIdKey . '#'));
        $this->setSessionSalt('salt' . $testDataIdKey);
        $this->setDisplayName('user' . $testDataIdKey);

        $this->setFirstName($faker->firstName);
        $this->setLastName($faker->lastName);
        $this->setGender($faker->randomElement(['male', 'female', 'other']));
        $this->setCountry($faker->country);
        $this->setRegion($faker->word);
        $this->setCity($faker->city);
        $this->setBirthday(Carbon::instance($faker->dateTime));
        $this->setPhoneNumber($faker->phoneNumber);
        $this->setBiography($faker->paragraphs(5, true));
        $this->setProfilePictureUrl($faker->imageUrl());
        $this->setTimezone($faker->randomElement(timezone_identifiers_list()));
        $this->setPermissionLevel($faker->randomElement([null, 'moderator', 'administrator']));

        $this->setNotifyOnLessonCommentReply($faker->boolean);
        $this->setNotifyWeeklyUpdate($faker->boolean);
        $this->setNotifyOnForumPostLike($faker->boolean);
        $this->setNotifyOnForumFollowedThreadReply($faker->boolean);
        $this->setNotifyOnLessonCommentLike($faker->boolean);

        $this->setLegacyDrumeoWordpressId(rand());
        $this->setLegacyDrumeoIpbId(rand());

        $this->setDrumsPlayingSinceYear(rand(1900, 2050));
        $this->setDrumsGearPhoto($faker->imageUrl());
        $this->setDrumsGearCymbalBrands($faker->words(rand(1, 5), true));
        $this->setDrumsGearSetBrands($faker->words(rand(1, 5), true));
        $this->setDrumsGearHardwareBrands($faker->words(rand(1, 5), true));
        $this->setDrumsGearStickBrands($faker->words(rand(1, 5), true));

        $this->setGuitarPlayingSinceYear(rand(1900, 2050));
        $this->setGuitarGearPhoto($faker->imageUrl());
        $this->setGuitarGearGuitarBrands($faker->words(rand(1, 5), true));
        $this->setGuitarGearAmpBrands($faker->words(rand(1, 5), true));
        $this->setGuitarGearPedalBrands($faker->words(rand(1, 5), true));
        $this->setGuitarGearStringBrands($faker->words(rand(1, 5), true));

        $this->setPianoPlayingSinceYear(rand(1900, 2050));
        $this->setPianoGearPhoto($faker->imageUrl());
        $this->setPianoGearPianoBrands($faker->words(rand(1, 5), true));
        $this->setPianoGearKeyboardBrands($faker->words(rand(1, 5), true));
    }
}