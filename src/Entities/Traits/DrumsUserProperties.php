<?php

namespace Railroad\Usora\Entities\Traits;

trait DrumsUserProperties
{

    /**
     * @ORM\Column(type="integer")
     * @var string
     */
    protected $legacyDrumeoWordpressId;

    /**
     * @ORM\Column(type="integer")
     * @var string
     */
    protected $legacyDrumeoIpbId;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $drumsPlayingSinceYear;

    /**
     * @ORM\Column(type="url")
     * @var string
     */
    protected $drumsGearPhoto;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $drumsGearCymbalBrands;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $drumsGearSetBrands;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $drumsGearHardwareBrands;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $drumsGearStickBrands;

    /**
     * @return string
     */
    public function getLegacyDrumeoWordpressId()
    {
        return $this->legacyDrumeoWordpressId;
    }

    /**
     * @param string $legacyDrumeoWordpressId
     */
    public function setLegacyDrumeoWordpressId($legacyDrumeoWordpressId)
    {
        $this->legacyDrumeoWordpressId = $legacyDrumeoWordpressId;
    }

    /**
     * @return string
     */
    public function getLegacyDrumeoIpbId()
    {
        return $this->legacyDrumeoIpbId;
    }

    /**
     * @param string $legacyDrumeoIpbId
     */
    public function setLegacyDrumeoIpbId($legacyDrumeoIpbId)
    {
        $this->legacyDrumeoIpbId = $legacyDrumeoIpbId;
    }

    /**
     * @return int
     */
    public function getDrumsPlayingSinceYear()
    {
        return $this->drumsPlayingSinceYear;
    }

    /**
     * @param int $drumsPlayingSinceYear
     */
    public function setDrumsPlayingSinceYear($drumsPlayingSinceYear)
    {
        $this->drumsPlayingSinceYear = $drumsPlayingSinceYear;
    }

    /**
     * @return string
     */
    public function getDrumsGearPhoto()
    {
        return $this->drumsGearPhoto;
    }

    /**
     * @param string $drumsGearPhoto
     */
    public function setDrumsGearPhoto($drumsGearPhoto)
    {
        $this->drumsGearPhoto = $drumsGearPhoto;
    }

    /**
     * @return string
     */
    public function getDrumsGearCymbalBrands()
    {
        return $this->drumsGearCymbalBrands;
    }

    /**
     * @param string $drumsGearCymbalBrands
     */
    public function setDrumsGearCymbalBrands($drumsGearCymbalBrands)
    {
        $this->drumsGearCymbalBrands = $drumsGearCymbalBrands;
    }

    /**
     * @return string
     */
    public function getDrumsGearSetBrands()
    {
        return $this->drumsGearSetBrands;
    }

    /**
     * @param string $drumsGearSetBrands
     */
    public function setDrumsGearSetBrands($drumsGearSetBrands)
    {
        $this->drumsGearSetBrands = $drumsGearSetBrands;
    }

    /**
     * @return string
     */
    public function getDrumsGearHardwareBrands()
    {
        return $this->drumsGearHardwareBrands;
    }

    /**
     * @param string $drumsGearHardwareBrands
     */
    public function setDrumsGearHardwareBrands($drumsGearHardwareBrands)
    {
        $this->drumsGearHardwareBrands = $drumsGearHardwareBrands;
    }

    /**
     * @return string
     */
    public function getDrumsGearStickBrands()
    {
        return $this->drumsGearStickBrands;
    }

    /**
     * @param string $drumsGearStickBrands
     */
    public function setDrumsGearStickBrands($drumsGearStickBrands)
    {
        $this->drumsGearStickBrands = $drumsGearStickBrands;
    }
}