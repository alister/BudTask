<?php

namespace Tests;

use Alister\Bud\DeathStarApiClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class DeathStarApiClientTest extends TestCase
{
    const CLIENT_SECRET = 'Alderaan';
    const CLIENT_ID = 'R2D2';

    public function testGetTokenSuccessfullyMocked()
    {
        // randomise what we expect to get back to assure ourselves it's being passed back exactly.
        $randomisedAcessToken = base64_encode(random_bytes(16));
        $goodResponse = [
            'access_token' => $randomisedAcessToken,
            'expires_in' => 99999999999,
            'token_type' => 'Bearer',
            'scope' => 'TheForce'
        ];

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, $goodResponse),
        ]);

        $handler = HandlerStack::create($mock);
        $guzzleClient = new Client(['handler' => $handler]);

        $client = new DeathStarApiClient($guzzleClient);
        $result = $client->getToken(self::CLIENT_ID, self::CLIENT_SECRET);

        // the headers are returned as arrays, so check for the '[ content ]'
        $this->assertSame($result->getHeader('access_token'), [$randomisedAcessToken]);
    }
}
