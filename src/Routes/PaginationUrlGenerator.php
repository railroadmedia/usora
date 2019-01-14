<?php

namespace Railroad\Usora\Routes;

class PaginationUrlGenerator
{
    public static function generate($page)
    {
        return request()->fullUrlWithQuery(['page' => $page]);
    }
}