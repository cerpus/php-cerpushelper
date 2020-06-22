<?php

namespace Cerpus\Helper\Middleware;

use Carbon\Carbon;
use Cerpus\LaravelAuth\Service\CerpusAuthService;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Verifies that the request contains a token with "client_credentials" grant type and "read" scope.
 * Use in front of machine-to-machine api endpoints.
 *
 * Class ValidClientCredentialsToken
 * @package Cerpus\Helper\Middleware
 */
class ValidClientCredentialsToken
{
    protected $requiredScopes = [
        'read',
    ];

    protected $bearerToken = null;
    protected $tokenResponse = null;

    protected $tokenResponseCacheTime = 1800;

    public function handle($request, Closure $next)
    {
        try {
            if (!$bearerToken = $request->bearerToken()) {
                return response()->json([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'message' => "Missing Bearer token."
                ], Response::HTTP_UNAUTHORIZED);
            }

            $this->setBearerToken($bearerToken);

            if ($this->bearerTokenIsNotActive()) {
                return response()->json([
                    'code' => Response::HTTP_FORBIDDEN,
                    'message' => "Inactive token."
                ], Response::HTTP_FORBIDDEN);
            }

            if ($this->bearerTokenGrantTypeIs('client_credentials')) { // Machine to machine token with the appropriate scopes
                if ($this->bearerTokenDoesNotHaveRequiredScopes()) {
                    return response()->json([
                        'code' => Response::HTTP_FORBIDDEN,
                        'message' => "Token is missing the required scope(s): " . implode(',', $this->requiredScopes),
                    ], Response::HTTP_FORBIDDEN);
                }
            } else {
                return response()->json([
                    'code' => Response::HTTP_FORBIDDEN,
                    'message' => "Token has the wrong grant type."
                ], Response::HTTP_FORBIDDEN);
            }
        } catch (\Throwable $t) {
            Log::error(__METHOD__ . '[' . $t->getLine() . '] (' . $t->getCode() . '): ' . $t->getMessage());

            return response()->json([
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => "Internal server error."
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        //Everything checks out, continue
        return $next($request);
    }

    protected function bearerTokenGrantTypeIs($grantType = []): bool
    {
        $grantTypeMatches = false;

        if (is_string($grantType)) {
            $grantType = explode(',', $grantType);
        }

        if ($checkTokenResponse = $this->getTokenResponse()) {
            $checkGrant = mb_strtolower($checkTokenResponse->getGrantType());
            $grantTypeMatches = in_array($checkGrant, $grantType);
        }

        return $grantTypeMatches;
    }

    protected function bearerTokenDoesNotHaveRequiredScopes(): bool
    {
        $hasRequiredScopes = false;

        if ($checkTokenResponse = $this->getTokenResponse()) {
            $checkedScopes = array_intersect($this->requiredScopes, $checkTokenResponse->getScope());
            $hasRequiredScopes = (bool)(count($checkedScopes) === count($this->requiredScopes));
        }

        return !$hasRequiredScopes;
    }

    protected function bearerTokenIsNotActive()
    {
        $tokenIsActive = false;

        if ($checkTokenResponse = $this->getTokenResponse()) {
            $tokenIsActive = $checkTokenResponse->isActive();
        }

        return !$tokenIsActive;
    }

    protected function getTokenResponse()
    {
        if ($this->tokenResponse) {
            return $this->tokenResponse;
        }

        try {
            $cacheKey = "CerpusHelperCCTokenMiddleware|{$this->getBearerToken()}";

            if (!$tokenResponse = Cache::get($cacheKey, null)) {
                /** @var CerpusAuthService $authService */
                $authService = app(CerpusAuthService::class);
                if ($tokenResponse = $authService->getCheckTokenRequest($this->getBearerToken())->execute()) {
                    $cacheUntil = now()->addSeconds($this->tokenResponseCacheTime);

                    if ($tokenResponse->getExpiry()) {
                        $cacheUntil = Carbon::createFromTimestamp($tokenResponse->getExpiry())->subSeconds(10);
                    }

                    Cache::put($cacheKey, $tokenResponse, $cacheUntil);
                }
            }

            if ($tokenResponse) {
                $this->tokenResponse = $tokenResponse;
            }
        } catch (\Throwable $t) {
            Log::error(__METHOD__ . "[{$t->getLine()}]: (" . $t->getCode() . ')' . $t->getMessage());

            throw $t;
        }

        return $this->tokenResponse;
    }

    public function getBearerToken()
    {
        return $this->bearerToken;
    }

    public function setBearerToken($bearerToken)
    {
        $this->bearerToken = $bearerToken;
    }
}
