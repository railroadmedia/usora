<?php

namespace Railroad\Usora\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Railroad\Usora\Services\ConfigService;

abstract class RepositoryBase
{
    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Connection
     */
    public static $connectionMask;

    /**
     * CategoryRepository constructor.
     */
    public function __construct()
    {
        $this->databaseManager = app('db');

        if (empty(self::$connectionMask)) {
            /**
             * @var $realConnection Connection
             */
            $realConnection = app('db')->connection(ConfigService::$databaseConnectionName);
            $realConfig = $realConnection->getConfig();

            $realConfig['name'] = ConfigService::$connectionMaskPrefix . $realConfig['name'];

            $maskConnection =
                new Connection(
                    $realConnection->getPdo(),
                    $realConnection->getDatabaseName(),
                    $realConnection->getTablePrefix(),
                    $realConfig
                );

            if (!empty($realConnection->getSchemaGrammar())) {
                $maskConnection->setSchemaGrammar($realConnection->getSchemaGrammar());
            }

            $maskConnection->setQueryGrammar($realConnection->getQueryGrammar());
            $maskConnection->setEventDispatcher($realConnection->getEventDispatcher());
            $maskConnection->setPostProcessor($realConnection->getPostProcessor());

            self::$connectionMask = $maskConnection;
        }

        $this->connection = self::$connectionMask;
    }

    /**
     * @param integer $id
     * @return array|null
     */
    public function getById($id)
    {
        return $this->query()->where(['id' => $id])->first();
    }

    /**
     * @param integer $id
     * @return array
     */
    public function getByIds($ids)
    {
        return $this->query()->whereIn('id', $ids)->get();
    }

    /**
     * @param array $attributes
     * @return array|null
     */
    public function getFirstBy(array $attributes)
    {
        return $this->query()->where($attributes)->first();
    }

    /**
     * Returns new record id.
     *
     * @param array $data
     * @return int
     */
    public function create(array $data)
    {
        $existing = $this->query()->where($data)->first();

        if (empty($existing)) {
            return $this->query()->insertGetId($data);
        }

        return $existing['id'];
    }

    /**
     * @param integer $id
     * @param array $data
     * @return integer
     */
    public function update($id, array $data)
    {
        $existing = $this->query()->where(['id' => $id])->first();

        if (!empty($existing)) {
            $this->query()->where(['id' => $id])->update($data);
        }

        return $id;
    }

    /**
     * @param array $attributes
     * @param array $values
     * @param string $getterColumn
     * @return int|null
     */
    public function updateOrCreate(array $attributes, array $values = [], $getterColumn = 'id')
    {
        dd($attributes);
        $this->query()->updateOrInsert($attributes, $values);

        return $this->query()->where($attributes)->get([$getterColumn])->first()[$getterColumn] ?? null;
    }

    /**
     * Delete a record.
     *
     * @param integer $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->query()->where(['id' => $id])->delete() > 0;
    }

    /**
     * @return Builder
     */
    protected abstract function query();

    /**
     * @return Connection
     */
    protected function connection()
    {
        return $this->connection;
    }
}