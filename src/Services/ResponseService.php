<?php

namespace Railroad\Usora\Services;

use Doctrine\ORM\QueryBuilder;
use League\Fractal\Serializer\JsonApiSerializer;
use Railroad\Doctrine\Services\FractalResponseService;
use Railroad\Usora\Transformers\UserTransformer;
use Spatie\Fractal\Fractal;

class ResponseService extends FractalResponseService
{
    /**
     * @param $entityOrEntities
     * @param QueryBuilder|null $queryBuilder
     * @param array $includes
     * @return Fractal
     */
    public static function user($entityOrEntities, QueryBuilder $queryBuilder = null, array $includes = [])
    {
        return self::create($entityOrEntities, 'user', new UserTransformer(), new JsonApiSerializer(), $queryBuilder)
            ->parseIncludes($includes);
    }
}