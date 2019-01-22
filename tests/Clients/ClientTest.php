<?php

namespace Cerpus\HelperTests\Clients;

use Cerpus\Helper\Clients\Client;
use Cerpus\Helper\DataObjects\OauthSetup;
use Cerpus\HelperTests\Utils\HelperTestCase;
use Cerpus\HelperTests\Utils\Traits\WithFaker;

class ClientTest extends HelperTestCase
{
    use WithFaker;

    /**
     * @test
     */
    public function getClient()
    {
        $uri = $this->faker->url;
        $client = Client::getClient(OauthSetup::create([
            'coreUrl' => $uri
        ]));

        $this->assertInstanceOf(\GuzzleHttp\Client::class, $client);
        $this->assertEquals($uri, $client->getConfig('base_uri'));
    }
}
