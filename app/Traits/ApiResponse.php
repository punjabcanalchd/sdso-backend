<?php

namespace App\Traits;

trait ApiResponse
{
    /* ------------------------------------------------------------------
     * SUCCESS RESPONSE
     * ---------------------------------------------------------------- */

    protected function successResponse(mixed $data = null,string $message = 'Success',int $status = 200,array $addtionalDetails = []) {

        return response()->json([

            'success' => true,

            'message' => $message,

            'data' => $data,

            'addtionalDetails' => $addtionalDetails

        ], $status);
    }

    /* ------------------------------------------------------------------
     * ERROR RESPONSE
     * ---------------------------------------------------------------- */

    protected function errorResponse(string $message = 'Something went wrong',int $status = 400,mixed $errors = null) {

        return response()->json([

            'success' => false,

            'message' => $message,

            'errors' => $errors

        ], $status);
    }

    /* ------------------------------------------------------------------
     * PAGINATION RESPONSE
     * ---------------------------------------------------------------- */

    protected function paginatedResponse($paginator, string $message = 'Data fetched successfully', $draw = 1)
    {
        return response()->json([
            'draw' => (int) $draw,
            'recordsTotal' => $paginator->total(),
            'recordsFiltered' => $paginator->total(), // Change if search/filter is applied
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ]
        ]);
    }
}