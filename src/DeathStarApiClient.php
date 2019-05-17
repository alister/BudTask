<?php
declare(strict_types=1);

namespace Alister\Bud;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class DeathStarApiClient
{
    public const API_BASE_URI = 'https://death.star.api';

    /** @var \GuzzleHttp\ClientInterface */
    private $guzzleClient;
    /** @var string */
    private $bearerToken;

    public function __construct(ClientInterface $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken(string $clientId, string $clientSecret): ResponseInterface
    {
        $options = [
            'auth' => [$clientId, $clientSecret],
        ];
        return $this->guzzleClient->request('POST', '/token', $options);
    }

    public function setBearerToken(string $bearerToken): self
    {
        $this->bearerToken = $bearerToken;

        return $this;
    }

    public function shootExhaustWithTorpedoes(int $exhaustPort, int $qtyTorpedoes): ResponseInterface
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken,
                'Content-Type'  => 'application/json',
                'X-Torpedoes'   => $qtyTorpedoes,
            ],
        ];

        return $this->guzzleClient->request('DELETE', '/reactor/exhaust/' . $exhaustPort, $options);
    }

    public function getPrisonerLocation(string $prisonerName): ResponseInterface
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken,
                'Content-Type'  => 'application/json',
            ],
        ];

        return $this->guzzleClient->request('GET', '/prisoner' . $prisonerName, $options);
    }
}
