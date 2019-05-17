<?php

namespace Tests;

use Alister\Bud\DeathStarApiClient;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\MessageInterface;

class DeathStarApiClientTest extends TestCase
{
    const CLIENT_ID = 'R2D2';
    const CLIENT_SECRET = 'Alderaan';
    const EXPECTED_API_URI_TOKEN = 'https://death.star.api/token';

    private $container = [];

    public function testGetTokenSuccessfullyMocked()
    {
        // randomise what we expect to get back to assure ourselves it's being passed back exactly.
        $randomisedAccessToken = base64_encode(random_bytes(16));
        $mockedResponse = [
            'access_token' => $randomisedAccessToken,
            'expires_in' => 99999999999,
            'token_type' => 'Bearer',
            'scope' => 'TheForce'
        ];
        $client = $this->createMockDeathStarApiClient($mockedResponse);

        $result = $client->getToken(self::CLIENT_ID, self::CLIENT_SECRET);

        // the headers are returned as arrays, so check for the '[ content ]'
        $this->getAssertHeaderSame($result, 'access_token', [$randomisedAccessToken]);

        $this->assertCount(1, $this->container);
        $guzzleTransaction = $this->container[0];

        /** @var GuzzleHttp\Psr7\Request $request */
        $request = $guzzleTransaction['request'];
        /** @var GuzzleHttp\Psr7\Response $response */
        $response = $guzzleTransaction['response'];

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(self::EXPECTED_API_URI_TOKEN, (string)$request->getUri());
        $this->assertSame(200, $response->getStatusCode());

        // base64_decode('UjJEMjpBbGRlcmFhbg==') === {CLIENT_ID}:{CLIENT_SECRET}
        #var_dump($request->getHeaders());die;
        $this->getAssertHeaderSame($request, 'Authorization', ['Basic UjJEMjpBbGRlcmFhbg==']);
    }

    /**
     * @param array $mockedResponsed
     */
    private function createMockDeathStarApiClient(array $mockedResponsed): DeathStarApiClient
    {
        $mock = new MockHandler([
            new Response(200, $mockedResponsed),
        ]);

        $history = Middleware::history($this->container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        // normally setup by config, and injected by DI
        $guzzleClient = new Client(['handler' => $stack, 'base_uri' => DeathStarApiClient::API_BASE_URI]);

        return new DeathStarApiClient($guzzleClient);
    }

    private function getAssertHeaderSame(MessageInterface $result, string $headerName, array $expectedContent): void
    {
        $this->assertSame($result->getHeader($headerName), $expectedContent);
    }
}
