<?php

namespace Railroad\Usora\Entities\Traits;

trait PianoUserProperties
{
    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $pianoPlayingSinceYear;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $pianoGearPhoto;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $pianoGearPianoBrands;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $pianoGearKeyboardBrands;

    /**
     * @return int
     */
    public function getPianoPlayingSinceYear()
    {
        return $this->pianoPlayingSinceYear;
    }

    /**
     * @param int $pianoPlayingSinceYear
     */
    public function setPianoPlayingSinceYear($pianoPlayingSinceYear)
    {
        $this->pianoPlayingSinceYear = $pianoPlayingSinceYear;
    }

    /**
     * @return string
     */
    public function getPianoGearPhoto()
    {
        return $this->pianoGearPhoto;
    }

    /**
     * @param string $pianoGearPhoto
     */
    public function setPianoGearPhoto($pianoGearPhoto)
    {
        $this->pianoGearPhoto = $pianoGearPhoto;
    }

    /**
     * @return string
     */
    public function getPianoGearPianoBrands()
    {
        return $this->pianoGearPianoBrands;
    }

    /**
     * @param string $pianoGearPianoBrands
     */
    public function setPianoGearPianoBrands($pianoGearPianoBrands)
    {
        $this->pianoGearPianoBrands = $pianoGearPianoBrands;
    }

    /**
     * @return string
     */
    public function getPianoGearKeyboardBrands()
    {
        return $this->pianoGearKeyboardBrands;
    }

    /**
     * @param string $pianoGearKeyboardBrands
     */
    public function setPianoGearKeyboardBrands($pianoGearKeyboardBrands)
    {
        $this->pianoGearKeyboardBrands = $pianoGearKeyboardBrands;
    }
}