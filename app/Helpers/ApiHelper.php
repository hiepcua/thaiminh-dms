<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ApiHelper
{
    static function responseError($code = Response::HTTP_INTERNAL_SERVER_ERROR, $response = [], $error = null)
    {
        if ($error instanceof \Exception) {
            Log::error($error?->methordError . ' error: ' . $error->getMessage());
            Log::error($error);
        }

        $response = $response ?? [
            'result' => false,
            'message' => 'Server has an error. Please try again!'
        ];

        return response()->json($response, $code);
    }
}
