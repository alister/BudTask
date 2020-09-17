<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Alister\Bud;

use Alister\Bud\DeathStarApiClient;
use Alister\Bud\BinaryToGalacticBasic;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class FindLeiaTest extends TestCase
{
    private $container = [];

    public function testGetPrincessLocation(): void
    {
        $randomisedBearerToken = base64_encode(random_bytes(16));
        $mockedResponse = [
            'cell' => '01001110 01100101 01110110 01100101 01110010 00100000 01100111 01101111 01101110 01101110 01100001 00100000 01100111 01101001 01110110 01100101 00100000 01111001 01101111 01110101 00100000 01110101 01110000',
            'block' => '01001110 01100101 01110110 01100101 01110010 00100000 01100111 01101111 01101110 01101110 01100001 00100000 01101100 01100101 01110100 00100000 01111001 01101111 01110101 00100000 01100100 01101111 01110111 01101110'
            // you're welcome.
        ];
        $client = $this->createMockDeathStarApiClient([], json_encode($mockedResponse));
        $client->setBearerToken($randomisedBearerToken);

        $result = $client->getPrisonerLocation('leia');
        $this->assertJson((string)$result->getBody());

        $cellBlock = $client->getPrisonerLocationInGalacticBasic('leia');
        $this->assertArrayHasKey('cell', $cellBlock);
        $this->assertArrayHasKey('block', $cellBlock);

        $this->assertSame('e', $cellBlock['cell'][3]);
        $this->assertSame('r', $cellBlock['block'][4]);
    }

    private function createMockDeathStarApiClient(array $mockedResponseHeaders, $body = ''): DeathStarApiClient
    {
        $mock = new MockHandler([
            new Response(200, $mockedResponseHeaders, $body),
            new Response(200, $mockedResponseHeaders, $body),   // called twice in test
        ]);

        $history = Middleware::history($this->container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        // normally setup by config, and injected by DI
        $guzzleClient = new Client(['handler' => $stack, 'base_uri' => DeathStarApiClient::API_BASE_URI]);

        return new DeathStarApiClient($guzzleClient, new BinaryToGalacticBasic());
    }
}
