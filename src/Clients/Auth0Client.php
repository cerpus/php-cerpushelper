<?php

namespace Cerpus\Helper\Clients;


use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Header\AuthorizationBearer;
use Auth0\SDK\Exception\ApiException;
use Cerpus\Helper\Contracts\HelperClientContract;
use Cerpus\Helper\DataObjects\OauthSetup;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;

/**
 * Class Oauth2Client
 * @package Cerpus\Helper\Clients
 */
class Auth0Client implements HelperClientContract
{
    /**
     * @param OauthSetup $config
     * @return Client
     */
    public static function getClient(OauthSetup $config): ClientInterface
    {
        $auth0Setup = self::setup($config);

        $stack = HandlerStack::create();
        $stack->push($auth0Setup);

        $client = new Client([
            'base_uri' => $config->coreUrl,
            'handler' => $stack,
            RequestOptions::AUTH => 'oauth',
        ]);
        return $client;
    }

    /*
     *
     * Using default Auth0.
     *
     */
    protected static function setup(OauthSetup $config)
    {
        return function (callable $handler) use ($config) {
            return function (
                RequestInterface $request,
                array $options
            ) use ($handler, $config) {
                $cacheKey = md5($config->toJson());
                if (!\Cache::has($cacheKey)) {
                    $parsedUrl = parse_url($config->authUrl);
                    $domain = array_key_exists('host', $parsedUrl) ? $parsedUrl['host'] : $config->authUrl;
                    $auth0_api = new Authentication($domain, $config->key, $config->secret, $config->audience);

                    try {
                        $result = $auth0_api->client_credentials([]);
                    } catch (ClientException $e) {
                        \Log::error('Caught: ClientException - ' . $e->getMessage());
                        throw $e;
                    } catch (ApiException $e) {
                        \Log::error('Caught: ApiException - ' . $e->getMessage());
                        throw $e;
                    }

                    $expire = !empty($result['expire']) ? ($result['expire'] / 60) - 1 : null;
                    \Cache::put($cacheKey, $result['access_token'], $expire);
                }

                $header = new AuthorizationBearer(\Cache::get($cacheKey));
                $request = $request->withHeader($header->getHeader(), $header->getValue());
                return $handler($request, $options);
            };
        };
    }
}
