<?php

namespace Cerpus\Helper\Contracts;

use Cerpus\Helper\DataObjects\OauthSetup;
use GuzzleHttp\ClientInterface;

/**
 * Interface HelperClientContract
 * @package Cerpus\Helper\Contracts
 */
interface HelperClientContract
{
    /**
     * @param OauthSetup $config
     * @return ClientInterface
     */
    public static function getClient(OauthSetup $config): ClientInterface;
}