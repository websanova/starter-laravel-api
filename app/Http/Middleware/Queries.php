<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class Queries
{
    /**
     * Append executed database queries to the JSON response when enabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.queries')) {
            return $next($request);
        }

        DB::enableQueryLog();

        $response = $next($request);

        if ($response instanceof JsonResponse && is_object($data = $response->getData())) {
            $data->queries = DB::getQueryLog();
            $response->setData($data);
        }

        return $response;
    }
}
