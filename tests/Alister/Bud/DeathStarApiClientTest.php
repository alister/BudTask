<?php

namespace Tests;

use Alister\Bud\DeathStarApiClient;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

class DeathStarApiClientTest extends TestCase
{
    const CLIENT_SECRET = 'Alderaan';
    const CLIENT_ID = 'R2D2';
    const EXPECTED_API_URI_TOKEN = 'https://death.star.api/token';

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
        $mock = new MockHandler([
            new Response(200, $goodResponse),
        ]);

        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        // normally setup by config, and injected by DI
        $guzzleClient = new Client(['handler' => $stack, 'base_uri' => DeathStarApiClient::API_BASE_URI]);

        $client = new DeathStarApiClient($guzzleClient);
        $result = $client->getToken(self::CLIENT_ID, self::CLIENT_SECRET);

        // the headers are returned as arrays, so check for the '[ content ]'
        $this->assertSame($result->getHeader('access_token'), [$randomisedAcessToken]);

        $this->assertCount(1, $container);
        $guzzleTransaction = $container[0];

        /** @var GuzzleHttp\Psr7\Request $request */
        $request = $guzzleTransaction['request'];
        /** @var GuzzleHttp\Psr7\Response $response */
        $response = $guzzleTransaction['response'];

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(self::EXPECTED_API_URI_TOKEN, (string)$request->getUri());
        $this->assertSame(200, $response->getStatusCode());
    }
}
