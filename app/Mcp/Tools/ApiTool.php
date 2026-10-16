<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

abstract class ApiTool extends Tool
{
    protected string $method;

    protected string $uri;

    /**
     * Run the tool as a normal request against the matching API route.
     */
    public function handle(Request $request): Response
    {
        $arguments = $request->all();

        $uri = preg_replace_callback('/\{(\w+)\}/', function (array $matches) use (&$arguments) {
            return rawurlencode((string) Arr::pull($arguments, $matches[1]));
        }, $this->uri);

        $original = app('request');

        $apiRequest = HttpRequest::create($uri, $this->method, $arguments);
        $apiRequest->headers->set('Accept-Language', $original->headers->get('Accept-Language', ''));

        Auth::guard('sanctum')->setUser($request->user());

        try {
            $response = app(Kernel::class)->handle($apiRequest);
        } finally {
            app()->instance('request', $original);
        }

        return $this->toMcpResponse($response);
    }

    /**
     * Pass a successful body through, and turn any error into a readable MCP error.
     */
    protected function toMcpResponse(HttpResponse $response): Response
    {
        if ($response->isSuccessful()) {
            return Response::text($response->getContent() ?: 'Done.');
        }

        $body = (array) json_decode($response->getContent(), true);
        $errors = collect(Arr::get($body, 'errors', []))->flatten();

        return Response::error(
            $errors->isNotEmpty() ? $errors->implode(' ') : Arr::get($body, 'message', 'Request failed.')
        );
    }
}
