<?php

namespace Railroad\Usora\Entities\Traits;

trait GuitarUserProperties
{
    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $guitarPlayingSinceYear;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $guitarGearPhoto;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $guitarGearGuitarBrands;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $guitarGearAmpBrands;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $guitarGearPedalBrands;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $guitarGearStringBrands;

    /**
     * @return int
     */
    public function getGuitarPlayingSinceYear()
    {
        return $this->guitarPlayingSinceYear;
    }

    /**
     * @param int $guitarPlayingSinceYear
     */
    public function setGuitarPlayingSinceYear($guitarPlayingSinceYear)
    {
        $this->guitarPlayingSinceYear = $guitarPlayingSinceYear;
    }

    /**
     * @return string
     */
    public function getGuitarGearPhoto()
    {
        return $this->guitarGearPhoto;
    }

    /**
     * @param string $guitarGearPhoto
     */
    public function setGuitarGearPhoto($guitarGearPhoto)
    {
        $this->guitarGearPhoto = $guitarGearPhoto;
    }

    /**
     * @return string
     */
    public function getGuitarGearGuitarBrands()
    {
        return $this->guitarGearGuitarBrands;
    }

    /**
     * @param string $guitarGearGuitarBrands
     */
    public function setGuitarGearGuitarBrands($guitarGearGuitarBrands)
    {
        $this->guitarGearGuitarBrands = $guitarGearGuitarBrands;
    }

    /**
     * @return string
     */
    public function getGuitarGearAmpBrands()
    {
        return $this->guitarGearAmpBrands;
    }

    /**
     * @param string $guitarGearAmpBrands
     */
    public function setGuitarGearAmpBrands($guitarGearAmpBrands)
    {
        $this->guitarGearAmpBrands = $guitarGearAmpBrands;
    }

    /**
     * @return string
     */
    public function getGuitarGearPedalBrands()
    {
        return $this->guitarGearPedalBrands;
    }

    /**
     * @param string $guitarGearPedalBrands
     */
    public function setGuitarGearPedalBrands($guitarGearPedalBrands)
    {
        $this->guitarGearPedalBrands = $guitarGearPedalBrands;
    }

    /**
     * @return string
     */
    public function getGuitarGearStringBrands()
    {
        return $this->guitarGearStringBrands;
    }

    /**
     * @param string $guitarGearStringBrands
     */
    public function setGuitarGearStringBrands($guitarGearStringBrands)
    {
        $this->guitarGearStringBrands = $guitarGearStringBrands;
    }
}