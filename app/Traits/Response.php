<?php

namespace App\Traits;

trait Response
{
    public function success($data = [], $message = 'Success', $meta = [])
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    public function error($message = 'Error', $errors = [], $code = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
