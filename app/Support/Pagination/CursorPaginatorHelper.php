<?php

namespace App\Support\Pagination;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;

class CursorPaginatorHelper
{
    public static function toResponse(CursorPaginator $paginator, callable $mapItem): JsonResponse
    {
        $nextCursor = $paginator->nextCursor()?->encode();
        $prevCursor = $paginator->previousCursor()?->encode();
        $nextPageUrl = $paginator->nextPageUrl();
        $prevPageUrl = $paginator->previousPageUrl();

        $data = $paginator->getCollection()->map($mapItem)->values();

        return response()->json([
            'data' => $data,
            'path' => $paginator->path(),
            'per_page' => $paginator->perPage(),
            'next_cursor' => $nextCursor,
            'next_page_url' => $nextPageUrl,
            'prev_cursor' => $prevCursor,
            'prev_page_url' => $prevPageUrl,
        ]);
    }
}
