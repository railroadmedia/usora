<?php

namespace Railroad\Usora\Services;

class ClientRelayService
{
    /**
     * @var string
     */
    private static $headBottom;

    /**
     * @var string
     */
    private static $bodyTop;

    /**
     * @var string
     */
    private static $bodyBottom;

    const SESSION_PREFIX = 'usora_';

    /**
     * @param $userId
     * @param $verificationToken
     * @param $domain
     */
    public static function authorizeUserOnDomain($userId, $verificationToken, $domain)
    {
        $urlPath = parse_url(route('authenticate.verification-token'))['path'] ?? '';

        self::addToBodyTop(
            '<img src="https://' .
            rtrim($domain, '/') .
            '/' . ltrim($urlPath, '/') . '?vt=' .
            urlencode($verificationToken) .
            '&uid=' .
            urlencode($userId) .
            '" style="display:none;" />'
        );
    }

    /**
     * @param $html
     */
    public static function addToHeadBottom($html)
    {
        self::$headBottom = session(self::SESSION_PREFIX . 'headBottom', '') . $html . "\n";

        session([self::SESSION_PREFIX . 'headBottom' => self::$headBottom]);
    }

    /**
     * @param $html
     */
    public static function addToBodyTop($html)
    {
        self::$bodyTop = session(self::SESSION_PREFIX . 'bodyTop', '') . $html . "\n";

        session([self::SESSION_PREFIX . 'bodyTop' => self::$bodyTop]);
    }

    /**
     * @param $html
     */
    public static function addToBodyBottom($html)
    {
        self::$bodyBottom = session(self::SESSION_PREFIX . 'bodyBottom', '') . $html . "\n";

        session([self::SESSION_PREFIX . 'bodyBottom' => self::$bodyBottom]);
    }

    /**
     * @param bool $clear
     * @return string
     */
    public static function getHeadBottom($clear = true)
    {
        $return = session(self::SESSION_PREFIX . 'headBottom', '');

        if ($clear) {
            session()->forget(self::SESSION_PREFIX . 'headBottom');
        }

        return $return;
    }

    /**
     * @param bool $clear
     * @return string
     */
    public static function getBodyTop($clear = true)
    {
        $return = session(self::SESSION_PREFIX . 'bodyTop', '');

        if ($clear) {
            session()->forget(self::SESSION_PREFIX . 'bodyTop');
        }

        return $return;
    }

    /**
     * @param bool $clear
     * @return string
     */
    public static function getBodyBottom($clear = true)
    {
        $return = session(self::SESSION_PREFIX . 'bodyBottom', '');

        if ($clear) {
            session()->forget(self::SESSION_PREFIX . 'bodyBottom');
        }

        return $return;
    }
}