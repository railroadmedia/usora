<?php

namespace Railroad\Usora\Repositories;


use Illuminate\Database\Query\Builder;
use Railroad\Usora\Services\ConfigService;

class UserRepository extends RepositoryBase
{
    protected function query()
    {
        return self::$connectionMask->table(ConfigService::$tableUsers);
    }
}