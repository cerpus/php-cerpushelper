<?php

namespace Cerpus\Helper\Clients;


use Cerpus\Helper\Contracts\HelperClientContract;
use Cerpus\Helper\DataObjects\OauthSetup;
use GuzzleHttp\ClientInterface;

/**
 * Class Client
 * @package Cerpus\Helper\Clients
 */
class Client implements HelperClientContract
{

    /**
     * @param OauthSetup $config
     * @return ClientInterface
     */
    public static function getClient(OauthSetup $config): ClientInterface
    {
        return new \GuzzleHttp\Client([
            'base_uri' => $config->coreUrl,
        ]);
    }
}