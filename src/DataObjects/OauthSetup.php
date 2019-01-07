<?php

namespace Cerpus\Helper\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class OauthSetup
 * @package Cerpus\Helper\DataObjects
 *
 * @method static OauthSetup create($attributes = null)
 */
class OauthSetup
{
    use CreateTrait;

    /**
     * @var string $key
     * @var string $secret
     * @var string $coreUrl
     * @var string $authUrl
     * @var string $tokenSecret
     * @var string $token
     * @var string audience
     * @var string $scope
     */
    public $key, $secret, $coreUrl, $authUrl, $tokenSecret, $token, $audience, $scope;
}