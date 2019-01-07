<?php

namespace Cerpus\Helper\Clients;


use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Header\Authorization\AuthorizationBearer;
use Auth0\SDK\JWTVerifier;
use Cerpus\Helper\DataObjects\OauthSetup;
use Cerpus\Helper\Contracts\HelperClientContract;
use Cerpus\Helper\Middleware\Auth0Middleware;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use kamermans\OAuth2\GrantType\RefreshToken;
use kamermans\OAuth2\OAuth2Middleware;
use Psr\Http\Message\RequestInterface;

/**
 * Class Oauth2Client
 * @package Cerpus\Helper\Clients
 */
class Auth0Client implements HelperClientContract
{
    /**
     * @param OauthSetup $config
     * @return ClientInterface
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
                $parsedUrl = parse_url($config->authUrl);
                $domain = array_key_exists('host', $parsedUrl) ? $parsedUrl['host'] : $config->authUrl;
                $auth0_api = new Authentication($domain, $config->key, $config->secret, $config->audience);

                try {
                    $result = $auth0_api->client_credentials([]);
                } catch (ClientException $e) {
                    echo 'Caught: ClientException - ' . $e->getMessage();
                } catch (ApiException $e) {
                    echo 'Caught: ApiException - ' . $e->getMessage();
                }

                try {
                    $verifier = new JWTVerifier([
                        'supported_algs' => ['RS256'],
                        'valid_audiences' => [$config->audience],
                        'authorized_iss' => [sprintf('https://%s/', $domain)]
                    ]);

                    $decodedToken = $verifier->verifyAndDecode($result['access_token']);
                }
                catch(\Auth0\SDK\Exception\CoreException $e) {
                    throw $e;
                }

                $header = new AuthorizationBearer($result['access_token']);
                $request = $request->withHeader($header->getHeader(), $header->getValue());
                return $handler($request, $options);
            };
        };
    }
}