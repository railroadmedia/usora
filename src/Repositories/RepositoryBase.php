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
     * @return array
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
        return $this->query()->whereIn('id', $ids)->first();
    }

    /**
     * @param array $attributes
     * @param array $values
     * @param string $getterColumn
     * @return int|null
     */
    public function updateOrCreate(array $attributes, array $values = [], $getterColumn = 'id')
    {
        $this->query()->updateOrInsert($attributes, $values);

        return $this->query()->where($attributes)->get([$getterColumn])->first()[$getterColumn] ?? null;
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
     * @param $entity
     * @param string $positionColumnPrefix
     * @return bool
     */
    public function deleteAndReposition($entity, $positionColumnPrefix = '')
    {
        $existingLink = $this->query()
            ->where($entity)
            ->first();

        if (empty($existingLink)) {
            return true;
        }

        $query = $this->query();
        if(array_key_exists('content_id', $existingLink)){
            $query->where(
                [
                    'content_id' => $existingLink['content_id'],
                    'key' => $existingLink['key'],
                ]
            );
        }

        if(array_key_exists('parent_id', $existingLink)){
            $query->where('parent_id', $existingLink['parent_id']);
        }

        $query->where(
            $positionColumnPrefix . 'position',
            '>',
            $existingLink[$positionColumnPrefix . "position"]
        )
            ->decrement($positionColumnPrefix . 'position');

        $deleted = $this->query()
            ->where(['id' => $existingLink['id']])
            ->delete();

        return $deleted > 0;
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

    /**
     * @param $position
     * @param $dataCount
     * @param $existingData
     * @return mixed
     */
    private function recalculatePosition($position, $dataCount, $existingData)
    {
        if ($position === null || $position > $dataCount) {
            if (empty($existingData)) {
                $position = $dataCount + 1;
            } else {
                $position = $dataCount;
            }
        }

        if ($position < 1) {
            $position = 1;
        }

        return $position;
    }

    private function incrementOtherEntitiesPosition(
        $excludedEntityId = null,
        $contentId,
        $key,
        $startPosition,
        $endPosition = null
    ) {
        $query = $this->query()
            ->where('content_id', $contentId)
            ->where('key', $key)
            ->where('position', '>=', $startPosition);

        if ($excludedEntityId) {
            $query->where('id', '!=', $excludedEntityId);
        }

        if ($endPosition) {
            $query->where('position', '<', $endPosition);
        }

        return $query->increment('position') > 0;
    }

    private function decrementOtherEntitiesPosition(
        $excludedEntityId,
        $contentId,
        $key,
        $startPosition,
        $endPosition
    ) {
        return $this->query()
                ->where('content_id', $contentId)
                ->where('key', $key)
                ->where('id', '!=', $excludedEntityId)
                ->where('position', '>', $startPosition)
                ->where('position', '<=', $endPosition)
                ->decrement('position') > 0;
    }
}