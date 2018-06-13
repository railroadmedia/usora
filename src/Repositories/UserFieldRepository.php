<?php

namespace Railroad\Usora\Repositories;

use Railroad\Resora\Queries\CachedQuery;
use Railroad\Resora\Repositories\RepositoryBase;
use Railroad\Usora\Repositories\Queries\UserFieldQuery;
use Railroad\Usora\Services\ConfigService;

class UserFieldRepository extends RepositoryBase
{
    /**
     * @return CachedQuery|$this
     */
    protected function newQuery()
    {
        return (new UserFieldQuery($this->connection()))->from(ConfigService::$tableUserFields);
    }

    protected function connection()
    {
        return app('db')->connection(ConfigService::$databaseConnectionName);
    }
}