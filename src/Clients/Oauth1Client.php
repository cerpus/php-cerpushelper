<?php

namespace Cerpus\Helper\Clients;


use Cerpus\Helper\Contracts\HelperClientContract;
use Cerpus\Helper\DataObjects\OauthSetup;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Client;

/**
 * Class Oauth1Client
 * @package Cerpus\Helper\Clients
 */
class Oauth1Client implements HelperClientContract
{

    /**
     * @param OauthSetup $config
     * @return ClientInterface
     */
    public static function getClient(OauthSetup $config): ClientInterface
    {
        $stack = HandlerStack::create();

        $middleware = new Oauth1([
            'consumer_key' => $config->key,
            'consumer_secret' => $config->secret,
            'token' => $config->token,
            'token_secret' => $config->tokenSecret,
        ]);

        $stack->push($middleware);

        return new Client([
            'base_uri' => $config->coreUrl,
            'handler' => $stack,
            RequestOptions::AUTH => 'oauth',
        ]);
    }
}