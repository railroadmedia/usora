<?php

namespace Railroad\Usora\Repositories;

use Railroad\Resora\Queries\CachedQuery;
use Railroad\Resora\Repositories\RepositoryBase;
use Railroad\Usora\Services\ConfigService;

class EmailChangeRepository extends RepositoryBase
{
    /**
     * @return CachedQuery|$this
     */
    protected function newQuery()
    {
        return (new CachedQuery($this->connection()))->from(ConfigService::$tableEmailChanges);
    }

    protected function connection()
    {
        return app('db')->connection(ConfigService::$databaseConnectionName);
    }
}
