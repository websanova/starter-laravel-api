<?php

namespace App\Http\Controllers;

use App\Support\ServiceResult;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Render a failed ServiceResult. The error key travels as "error" so the
     * client can branch on the reason rather than parse the translated
     * message, which changes with the locale.
     */
    protected function error(ServiceResult $result, string $namespace, int $status = 409): JsonResponse
    {
        $response = [
            'error' => $result->error,
            'message' => __("responses.{$namespace}.{$result->error}"),
        ];

        if (config('app.debug') && isset($result->data['debug'])) {
            $response['debug'] = $result->data['debug'];
        }

        return response()->json($response, $status);
    }
}
