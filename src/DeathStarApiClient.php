<?php
declare(strict_types=1);

namespace Alister\Bud;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class DeathStarApiClient
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $guzzleClient;

    public function __construct(ClientInterface $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken(string $clientId, string $clientSecret): ResponseInterface
    {
        return $this->guzzleClient->request('POST', '/token');
    }
}
