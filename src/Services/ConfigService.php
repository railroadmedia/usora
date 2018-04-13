<?php

namespace Railroad\Usora\Services;

class ConfigService
{
    /**
     * @var string
     */
    public static $authenticationMode;

    /**
     * @var string
     */
    public static $databaseConnectionName;
    
    /**
     * @var string
     */
    public static $connectionMaskPrefix;

    /**
     * @var string
     */
    public static $tablePrefix;

    /**
     * @var string
     */
    public static $tableUsers;

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
}