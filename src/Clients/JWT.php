<?php


namespace Cerpus\Helper\Clients;


use Cerpus\AuthCore\AuthServiceConfig;
use Cerpus\AuthCore\JWTService;
use Cerpus\Helper\Contracts\HelperClientContract;
use Cerpus\Helper\DataObjects\OauthSetup;
use Cerpus\Helper\Helpers\Session;
use Cerpus\Helper\Managers\JWTManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;

class JWT implements HelperClientContract
{

    /**
     * @inheritDoc
     */
    public static function getClient(OauthSetup $config): ClientInterface
    {
        // Create a HandlerStack
        $stack = HandlerStack::create();
        // Add middleware
        $authConfig = (new AuthServiceConfig())
            ->setUrl($config->authUrl)
            ->setClientId($config->authUser)
            ->setSecret($config->authSecret);

        $stack->push(new JWTManager(new JWTService($authConfig, new Session())));

        return new Client(['handler' => $stack, 'base_uri' => $config->coreUrl]);
    }
}
