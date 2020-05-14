<?php

namespace Railroad\Usora\Entities\Traits;

trait UserInterfaceSettingsProperties
{
    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    protected $useLegacyVideoPlayer = false;

    /**
     * @return bool
     */
    public function getUseLegacyVideoPlayer()
    {
        return $this->useLegacyVideoPlayer;
    }

    /**
     * @param bool $useLegacyVideoPlayer
     */
    public function setUseLegacyVideoPlayer($useLegacyVideoPlayer): void
    {
        $this->useLegacyVideoPlayer = $useLegacyVideoPlayer;
    }
}