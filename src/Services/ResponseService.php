<?php

namespace Railroad\Usora\Services;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use League\Fractal\Pagination\DoctrinePaginatorAdapter;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\TransformerAbstract;
use Railroad\Usora\Routes\PaginationUrlGenerator;
use Railroad\Usora\Transformers\UserTransformer;
use Spatie\Fractal\Fractal;

class ResponseService
{
    /**
     * @param $userOrUsers
     * @param QueryBuilder|null $queryBuilder
     * @param array $includes
     * @return Fractal
     */
    public static function user($userOrUsers, QueryBuilder $queryBuilder = null, array $includes = [])
    {
        return self::create($userOrUsers, 'user', new UserTransformer, new JsonApiSerializer(), $queryBuilder)
            ->parseIncludes($includes);
    }

    /**
     * @param $dataOrDatum
     * @param $type
     * @param TransformerAbstract $transformer
     * @param ArraySerializer $serializer
     * @param QueryBuilder|null $queryBuilder
     * @return Fractal
     */
    public static function create(
        $dataOrDatum,
        $type,
        TransformerAbstract $transformer,
        ArraySerializer $serializer,
        QueryBuilder $queryBuilder = null
    ) {
        $response = fractal(null, $transformer, $serializer);

        // if we pass the array of entities directly in to the fractal constructor the type doesnt get set, so we must
        // use ->collection or ->item to set the data for the response
        if (is_iterable($dataOrDatum)) {
            $response->collection($dataOrDatum, null, $type);
        } else {
            $response->item($dataOrDatum, null, $type);
        }

        if (!is_null($queryBuilder)) {
            $response->paginateWith(
                new DoctrinePaginatorAdapter(
                    new Paginator($queryBuilder), [PaginationUrlGenerator::class, 'generate']
                )
            );
        }

        return $response;
    }
}