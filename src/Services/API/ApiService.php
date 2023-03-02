<?php

namespace Itsimiro\OpenAI\Services\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Itsimiro\OpenAI\Services\API\Auth\AuthServiceInterface;
use Itsimiro\OpenAI\Services\API\Results\ResultInterface;
use Psr\Http\Message\ResponseInterface;

class ApiService
{
    public function __construct(
        private readonly Client $client,
        private readonly AuthServiceInterface $authService,
        private readonly UrlService $urlService
    )
    {}

    /**
     * @throws GuzzleException
     */
    public function sendRequest(string $method, string $endpoint, array $parameters = []): ResponseInterface
    {
        $parameters['headers'] = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->authService->getToken(),
        ];

        return $this->client->request($method, $this->urlService->buildUrl($endpoint), [
            'json' => $parameters
        ]);
    }

    public function getResult(ResponseInterface $response, string $resultClass): ResultInterface
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode > 210) {
            throw new InvalidArgumentException('Invalid status code: ' . $statusCode);
        }

        $result = json_decode($response->getBody()->getContents(), true);

        if (!$result) {
            throw new InvalidArgumentException('Invalid response.');
        }

        return new $resultClass($result['data']);
    }
}