<?php

namespace Railroad\Usora\Entities\Traits;

use Carbon\Carbon;

trait BasicUserInformationProperties
{
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $lastName;

    /**
     * @ORM\Column(type="gender")
     * @var string
     */
    protected $gender;

    /**
     * @ORM\Column(type="string", length=84)
     * @var string
     */
    protected $country;

    /**
     * @ORM\Column(type="string", length=84)
     * @var string
     */
    protected $region;

    /**
     * @ORM\Column(type="string", length=84)
     * @var string
     */
    protected $city;

    /**
     * @ORM\Column(type="date")
     * @var Carbon
     */
    protected $birthday;

    /**
     * @ORM\Column(type="phone_number")
     * @var integer
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $biography;

    /**
     * @ORM\Column(type="url")
     * @var string
     */
    protected $profilePictureUrl;

    /**
     * @ORM\Column(type="timezone")
     * @var string
     */
    protected $timezone;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $permissionLevel;

    /**
     * @ORM\Column(type="array")
     * @var string
     */
    protected $firebaseTokenWeb;

    /**
     * @ORM\Column(type="array")
     * @var string
     */
    protected $firebaseTokenIOS;

    /**
     * @ORM\Column(type="array")
     * @var string
     */
    protected $firebaseTokenAndroid;

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param string $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return Carbon|null
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param Carbon|null $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return int
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param int $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = !empty($phoneNumber) ? preg_replace('/[^0-9]/', '', $phoneNumber) : $phoneNumber;
    }

    /**
     * @return string
     */
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * @param string $biography
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;
    }

    /**
     * @return string
     */
    public function getProfilePictureUrl()
    {
        return $this->profilePictureUrl ?? config('usora.default_profile_picture_url');
    }

    /**
     * @param string $profilePictureUrl
     */
    public function setProfilePictureUrl($profilePictureUrl)
    {
        $this->profilePictureUrl = $profilePictureUrl;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone ?? config('usora.default_timezone');
    }

    /**
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return string
     */
    public function getPermissionLevel()
    {
        return $this->permissionLevel;
    }

    /**
     * @param string $permissionLevel
     */
    public function setPermissionLevel($permissionLevel)
    {
        $this->permissionLevel = $permissionLevel;
    }

    /**
     * @return array|null
     */
    public function getFirebaseTokenWeb()
    : ?array
    {
        return $this->firebaseTokenWeb;
    }

    /**
     * @param array $firebaseTokenWeb
     */
    public function setFirebaseTokenWeb(array $firebaseTokenWeb)
    {
        $this->firebaseTokenWeb = $firebaseTokenWeb;
    }

    /**
     * @return array|null
     */
    public function getFirebaseTokenIOS()
    : ?array
    {
        return $this->firebaseTokenIOS;
    }

    /**
     * @param array $firebaseTokenIOS
     */
    public function setFirebaseTokenIOS(array $firebaseTokenIOS)
    {
        $this->firebaseTokenIOS = $firebaseTokenIOS;
    }

    /**
     * @return array|null
     */
    public function getFirebaseTokenAndroid()
    : ?array
    {
        return $this->firebaseTokenAndroid;
    }

    /**
     * @param array $firebaseTokenAndroid
     */
    public function setFirebaseTokenAndroid(array $firebaseTokenAndroid)
    {
        $this->firebaseTokenAndroid = $firebaseTokenAndroid;
    }
}