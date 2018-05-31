<?php

namespace Railroad\Usora\Repositories;

use Railroad\Resora\Decorators\Decorator;
use Railroad\Resora\Queries\CachedQuery;
use Railroad\Resora\Repositories\RepositoryBase;
use Railroad\Usora\Repositories\Queries\UserQuery;
use Railroad\Usora\Services\ConfigService;

class UserRepository extends RepositoryBase
{
    /**
     * @return UserQuery|$this
     */
    protected function newQuery()
    {
        return (new UserQuery($this->connection()))->from(ConfigService::$tableUsers);
    }

    protected function decorate($results)
    {
        return Decorator::decorate($results, 'users');
    }

    protected function connection()
    {
        return app('db')->connection(ConfigService::$databaseConnectionName);
    }
}