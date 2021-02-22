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
        if (!empty(config('usora.domains_to_authenticate_on_with_request_urls')[$domain])) {
            $baseUrl = config('usora.domains_to_authenticate_on_with_request_urls')[$domain]['with-verification-token'];
        } else {
            error_log('Usora error: domain to authenticate on is not configured properly');
            return;
        }

        self::addToBodyTop(
            '<img src="' .
            $baseUrl .
            '?vt=remember_token|' .
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