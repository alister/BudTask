<?php

declare(strict_types=1);

namespace Tests\Alister\Bud;

use Alister\Bud\DeathStarApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

class DeathStarApiClientTest extends TestCase
{
    public const CLIENT_ID = 'R2D2';
    public const CLIENT_SECRET = 'Alderaan';
    public const EXPECTED_API_URI_TOKEN = 'https://death.star.api/token';

    private $container = [];

    public function testGetTokenSuccessfullyMocked(): void
    {
        // randomise what we expect to get back to assure ourselves it's being passed back exactly.
        $randomisedAccessToken = base64_encode(random_bytes(16));
        $mockedResponse = [
            'access_token' => $randomisedAccessToken,
            'expires_in' => 99999999999,
            'token_type' => 'Bearer',
            'scope' => 'TheForce'
        ];
        $client = $this->createMockDeathStarApiClient($mockedResponse, '');

        $result = $client->getToken(self::CLIENT_ID, self::CLIENT_SECRET);
        $this->assertInstanceOf(Response::class, $result);

        // the headers are returned as arrays, so check for the '[ content ]'
        $this->assertHeaderSame($result, 'access_token', [$randomisedAccessToken]);

        $this->assertCount(1, $this->container);
        $guzzleTransaction = $this->container[0];

        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $guzzleTransaction['request'];
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $guzzleTransaction['response'];

        $this->assertRequestHasCertificate($request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(self::EXPECTED_API_URI_TOKEN, (string)$request->getUri());
        $this->assertSame(200, $response->getStatusCode());

        // base64_decode('UjJEMjpBbGRlcmFhbg==') === {CLIENT_ID}:{CLIENT_SECRET}
        $this->assertHeaderSame($request, 'Authorization', ['Basic UjJEMjpBbGRlcmFhbg==']);
    }

    public function testShootExhaustpost(): void
    {
        $randomisedBearerToken = base64_encode(random_bytes(16));

        $client = $this->createMockDeathStarApiClient([], '');
        $client->setBearerToken($randomisedBearerToken);

        $result = $client->shootExhaustWithTorpedoes(1, 2);
        $this->assertInstanceOf(Response::class, $result);

        $this->assertCount(1, $this->container);
        $guzzleTransaction = $this->container[0];

        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $guzzleTransaction['request'];
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $guzzleTransaction['response'];

        $this->assertRequestHasCertificate($request);
        $this->assertHeaderSame($request, 'Authorization', ["Bearer {$randomisedBearerToken}"]);
        $this->assertHeaderSame($request, 'X-Torpedoes', ['2']);
        $this->assertHeaderSame($request, 'Content-Type', ['application/json']);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetPrisonerLocation(): void
    {
        $randomisedBearerToken = base64_encode(random_bytes(16));
        $mockedResponse = [
            'cell' => 'xyz',
            'block' => 'abc'
        ];
        $client = $this->createMockDeathStarApiClient([], json_encode($mockedResponse));
        $client->setBearerToken($randomisedBearerToken);

        $result = $client->getPrisonerLocation('leia');
        $this->assertInstanceOf(Response::class, $result);

        $this->assertCount(1, $this->container);
        $guzzleTransaction = $this->container[0];

        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $guzzleTransaction['request'];
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $guzzleTransaction['response'];

        $this->assertRequestHasCertificate($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertHeaderSame($request, 'Authorization', ["Bearer {$randomisedBearerToken}"]);
        $this->assertHeaderSame($request, 'Content-Type', ['application/json']);

        $decodedData = \GuzzleHttp\json_decode($response->getBody(), true);
        $this->assertSame($mockedResponse, $decodedData);
    }

    private function createMockDeathStarApiClient(array $mockedResponseHeaders, $body = ''): DeathStarApiClient
    {
        $mock = new MockHandler([
            new Response(200, $mockedResponseHeaders, $body),
        ]);

        $history = Middleware::history($this->container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        // normally setup by config, and injected by DI
        $guzzleClient = new Client(['handler' => $stack, 'base_uri' => DeathStarApiClient::API_BASE_URI]);

        return new DeathStarApiClient($guzzleClient);
    }

    private function assertHeaderSame(MessageInterface $message, string $headerName, array $expectedContent): void
    {
        $this->assertSame($message->getHeader($headerName), $expectedContent);
    }

    private function assertRequestHasCertificate(Request $request): void
    {
        // @todo assure ourselves that the certificate is being sent as required
    }
}
