<?php


namespace Cerpus\Helper\Clients;


use Cerpus\LaravelAuth\Service\CerpusAuthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Simple interface for working with Cerpus Auth.
 * Anything that can be cached will be cached.
 *
 * Class CachingAuthClient
 * @package Cerpus\Helper\Clients
 */
class CachingAuthClient
{
    protected $authService;
    protected $ccToken = null;

    public function __construct(CerpusAuthService $authService)
    {
        $this->authService = $authService;
        if (!$authService) {
            $this->authService = app(CerpusAuthService::class);
        }
    }

    /**
     * Fetch a Client Credentials Token from Auth. Cache the token for as long as possible.
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function fetchCCToken()
    {
        if ($this->ccToken) {
            return $this->ccToken;
        }

        $cacheKey = "CerpusHelperClientsCachingAuthClientCCToken" . Str::camel(config("app.name", "nonameapp"));
        $cacheTime = 5;

        if (!$this->ccToken = Cache::get($cacheKey)) {
            $token = $this->authService->getClientCredentialsTokenRequest()->execute();
            $this->ccToken = $token->access_token ?? null;

            if ($token->expires_in ?? null) {
                $cacheTime = now()->addSeconds(max($cacheTime, ($token->expires_in - 10)));
            }

            if ($this->ccToken) {
                Cache::put($cacheKey, $this->ccToken, $cacheTime);
            }
        }

        return $this->ccToken;
    }
}
