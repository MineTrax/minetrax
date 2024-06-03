<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function success($data = null, $message = null, int $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function error($message, $type, int $status = 400)
    {
        return response()->json([
            'status' => 'error',
            'type' => $type,
            'message' => $message,
        ], $status);
    }

    public function noContent()
    {
        return response()->noContent();
    }
}
