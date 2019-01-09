<?php

namespace Railroad\Usora\Services;

class ConfigService
{
    /**
     * @var string
     */
    public static $dataMode;

    /**
     * @var string
     */
    public static $tablePrefix;

    /**
     * @var string
     */
    public static $tableUsers;

    /**
     * @var string
     */
    public static $tableUserFields;

    /**
     * @var string
     */
    public static $tableUserData;

    /**
     * @var string
     */
    public static $tablePasswordResets;

    /**
     * @var string
     */
    public static $tableEmailChanges;

    /**
     * @var array
     */
    public static $domainsToAuthenticateOn;

    /**
     * @var array
     */
    public static $domainsToCheckForAuthenticateOn;

    /**
     * @var string
     */
    public static $loginPagePath;

    /**
     * @var string
     */
    public static $loginSuccessRedirectPath;

    /**
     * @var bool
     */
    public static $rememberMe;

    /**
     * @var string
     */
    public static $passwordResetNotificationClass;

    /**
     * @var string
     */
    public static $passwordResetNotificationChannel;

    /**
     * @var string
     */
    public static $emailChangeNotificationClass;

    /**
     * @var string
     */
    public static $emailChangeNotificationChannel;

    /**
     * @var int
     */
    public static $emailChangeTtl;

    /**
     * @var array
     */
    public static $authenticationControllerMiddleware;
}