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
     * @var string
     */
    public static $loginPageUrl;

    /**
     * @var string
     */
    public static $loginSuccessRedirectUrl;
}