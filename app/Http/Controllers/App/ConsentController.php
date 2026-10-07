<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Consent\ShowRequest;
use App\Http\Requests\App\Consent\StoreRequest;
use Illuminate\Http\JsonResponse;
use Laravel\Passport\Bridge\User;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConsentController extends Controller
{
    public function __construct(protected AuthorizationServer $server) {}

    /**
     * Validate the authorize request the App received from the MCP Client and
     * return what the consent screen shows. An unknown client or a redirect_uri
     * mismatch comes back as an error, never as a redirect.
     */
    public function show(ShowRequest $request, ServerRequestInterface $psrRequest): JsonResponse
    {
        try {
            $authRequest = $this->server->validateAuthorizationRequest($psrRequest);
        } catch (OAuthServerException $e) {
            return $this->errorResponse($e);
        }

        return response()->json([
            'data' => [
                'client_name' => $authRequest->getClient()->getName(),
                'scope' => collect($authRequest->getScopes())
                    ->map(fn (ScopeEntityInterface $scope): string => $scope->getIdentifier())
                    ->implode(' '),
            ],
        ]);
    }

    /**
     * Record the User's decision and return the redirect_uri the App sends the
     * browser to. An approval carries the code and state, a denial carries
     * error=access_denied and state.
     */
    public function store(StoreRequest $request, ServerRequestInterface $psrRequest, ResponseInterface $psrResponse): JsonResponse
    {
        // Passport reads the request from the query string, and the App posts it as a body.
        $params = [...$request->safe()->except('approve'), 'response_type' => 'code'];

        try {
            $authRequest = $this->server->validateAuthorizationRequest($psrRequest->withQueryParams($params));
        } catch (OAuthServerException $e) {
            return $this->errorResponse($e);
        }

        $authRequest->setUser(new User($request->user()->getAuthIdentifier()));
        $authRequest->setAuthorizationApproved($request->boolean('approve'));

        try {
            $response = $this->server->completeAuthorizationRequest($authRequest, $psrResponse);
        } catch (OAuthServerException $e) {
            // A denial is thrown with the redirect_uri already carrying the error and state.
            if (!$e->hasRedirect()) {
                return $this->errorResponse($e);
            }

            $response = $e->generateHttpResponse($psrResponse);
        }

        return response()->json([
            'data' => [
                'redirect_uri' => $response->getHeaderLine('Location'),
            ],
        ]);
    }

    protected function errorResponse(OAuthServerException $e): JsonResponse
    {
        return response()->json([
            'error' => $e->getErrorType(),
            'message' => $e->getMessage(),
        ], $e->getHttpStatusCode());
    }
}
