<?php

namespace Railroad\Usora\Entities\Traits;

trait AppReviewProperties
{
    /**
     * @ORM\Column(type="datetime", name="ios_latest_review_display_date", nullable=true)
     * @var \DateTime
     */
    protected $iosLatestReviewDisplayDate;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $iosCountReviewDisplay;

    /**
     * @ORM\Column(type="datetime", name="google_latest_review_display_date", nullable=true)
     * @var \DateTime
     */
    protected $googleLatestReviewDisplayDate;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $googleCountReviewDisplay;

    /**
     * @return date|null
     */
    public function getIosLatestReviewDisplayDate()
    {
        return $this->iosLatestReviewDisplayDate;
    }

    /**
     * @param $iosLatestReviewDisplayDate
     */
    public function setIosLatestReviewDisplayDate($iosLatestReviewDisplayDate): void
    {
        $this->iosLatestReviewDisplayDate = $iosLatestReviewDisplayDate;
    }

    /**
     * @return int
     */
    public function getIosCountReviewDisplay()
    {
        return $this->iosCountReviewDisplay;
    }

    /**
     * @param $iosCountReviewDisplay
     */
    public function setIosCountReviewDisplay($iosCountReviewDisplay): void
    {
        $this->iosCountReviewDisplay = $iosCountReviewDisplay;
    }

    /**
     * @return date|null
     */
    public function getGoogleLatestReviewDisplayDate()
    {
        return $this->googleLatestReviewDisplayDate;
    }

    /**
     * @param $googleCountReviewDisplay
     */
    public function setGoogleLatestReviewDisplayDate($googleCountReviewDisplay): void
    {
        $this->googleLatestReviewDisplayDate = $googleCountReviewDisplay;
    }

    /**
     * @return int
     */
    public function getGoogleCountReviewDisplay()
    {
        return $this->googleCountReviewDisplay;
    }

    /**
     * @param $googleCountReviewDisplay
     */
    public function setGoogleCountReviewDisplay($googleCountReviewDisplay): void
    {
        $this->googleCountReviewDisplay = $googleCountReviewDisplay;
    }
}