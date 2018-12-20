<?php

namespace Cerpus\Helper\Clients\GrantTypes;

use Auth0\SDK\JWTVerifier;
use Cerpus\Helper\DataObjects\OauthSetup;
use GuzzleHttp\Post\PostBody;
use GuzzleHttp\ClientInterface;
use kamermans\OAuth2\GrantType\GrantTypeInterface;
use kamermans\OAuth2\Utils\Helper;
use kamermans\OAuth2\Utils\Collection;
use kamermans\OAuth2\Signer\ClientCredentials\SignerInterface;

/**
 * Client credentials grant type.
 *
 * @link http://tools.ietf.org/html/rfc6749#section-4.4
 */
class ClientCredentials implements GrantTypeInterface
{
    /**
     * The token endpoint client.
     *
     * @var ClientInterface
     */
    private $client;

    /**
     * Configuration settings.
     *
     * @var Collection
     */
    private $config;

    private $settings;

    /**
     * @param ClientInterface $client
     * @param array           $config
     */
    public function __construct(ClientInterface $client, OauthSetup $config)
    {
        $this->client = $client;
        $this->settings = $config;
        $this->config = Collection::fromConfig(
            $config->toArray(),
            // Defaults
            [
                'secret' => '',
                'scope' => '',
                'audience' => '',
            ],
            // Required
            [
                'key',
            ]
        );
    }

    public function getRawData(SignerInterface $clientCredentialsSigner, $refreshToken = null)
    {
        if (Helper::guzzleIs('>=', 6)) {
            $request = (new \GuzzleHttp\Psr7\Request('POST', $this->client->getConfig()['base_uri']))
                ->withBody($this->getPostBody())
                ->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        } else {
            $request = $this->client->createRequest('POST', null);
            $request->setBody($this->getPostBody());
        }

        $request = $clientCredentialsSigner->sign(
            $request,
            $this->config['key'],
            $this->config['secret']
        );

        $response = $this->client->send($request);
        $token = json_decode($response->getBody(), true);
        $this->validateResponse($token['access_token']);

        return $token;
    }

    /**
     * @return PostBody
     */
    protected function getPostBody()
    {
        if (Helper::guzzleIs('>=', '6')) {
            $data = [
                'grant_type' => 'client_credentials'
            ];

            if ($this->config['audience']) {
                $data['audience'] = $this->config['audience'];
            }

            if ($this->config['scope']) {
                $data['scope'] = $this->config['scope'];
            }

            return \GuzzleHttp\Psr7\stream_for(http_build_query($data, '', '&'));
        }

        $postBody = new PostBody();
        $postBody->replaceFields([
            'grant_type' => 'client_credentials'
        ]);

        if ($this->config['audience']) {
            $postBody->setField('audience', $this->config['audience']);
        }

        if ($this->config['scope']) {
            $postBody->setField('scope', $this->config['scope']);
        }

        return $postBody;
    }

    /**
     * @param string $token
     * @return array
     * @throws \Auth0\SDK\Exception\CoreException
     */
    protected function validateResponse(string $token)
    {
        try {
            $verifier = new JWTVerifier([
                'supported_algs' => ['RS256'],
                'valid_audiences' => [$this->config['audience']],
                'authorized_iss' => [$this->makeUrl()]
            ]);

            return $verifier->verifyAndDecode($token);
        }
        catch(\Auth0\SDK\Exception\CoreException $e) {
            throw $e;
        }
    }

    private function makeUrl()
    {
        return $this->settings->authUrl . "/";
    }
}
