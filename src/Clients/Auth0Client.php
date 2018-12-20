<?php

namespace Cerpus\Helper\Clients;


use Cerpus\Helper\Clients\GrantTypes\ClientCredentials;
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
        $reauth_client = new Client([
            'base_uri' => $config->authUrl . "/oauth/token",
        ]);
        $reauth_config = [
            "client_id" => $config->key,
            "client_secret" => $config->secret,
            "audience" => $config->audience,
        ];
        $grant_type = new ClientCredentials($reauth_client, $config);
        $oauth = new OAuth2Middleware($grant_type);

        $stack = HandlerStack::create();
        $stack->push($oauth);

        $client = new Client([
            'base_uri' => $config->coreUrl,
            'handler' => $stack,
            RequestOptions::AUTH => 'oauth',
        ]);
        return $client;
    }
}