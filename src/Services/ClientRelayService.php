<?php

namespace Railroad\Usora\Services;

class ClientRelayService
{
    /**
     * @var string
     */
    public static $headBottom;

    /**
     * @var string
     */
    public static $bodyTop;

    /**
     * @var string
     */
    public static $bodyBottom;

    /**
     * @param $userId
     * @param $verificationToken
     * @param $domain
     */
    public static function authorizeUserOnDomain($userId, $verificationToken, $domain)
    {
        self::addToBodyTop(
            '<img src="https://' .
            $domain .
            '/cookietest?v=' .
            urlencode($verificationToken) .
            '&uid' .
            urlencode($userId) .
            '" style="display:none;" />'
        );
    }

    /**
     * @param $html
     */
    public static function addToHeadBottom($html)
    {
        self::$headBottom .= "\n\n" . $html;
    }

    /**
     * @param $html
     */
    public static function addToBodyTop($html)
    {
        self::$bodyTop .= "\n\n" . $html;
    }

    /**
     * @param $html
     */
    public static function addToBodyBottom($html)
    {
        self::$bodyBottom .= "\n\n" . $html;
    }

    /**
     * @return string
     */
    public static function getHeadBottom()
    {
        return self::$headBottom;
    }

    /**
     * @return string
     */
    public static function getBodyBottom()
    {
        return self::$bodyBottom;
    }
}