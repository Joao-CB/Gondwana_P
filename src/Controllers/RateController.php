<?php
namespace Joao\ApiP\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class RateController
{
    private Client $client;
    private string $apiUrl;

    private const JSON_CONTENT_TYPE = 'application/json';

    public function __construct(Client $client = null, ?string $apiUrl = null)
    {
        // Use injected client or default
        $this->client = $client ?? new Client();
        $this->apiUrl = $apiUrl ?? ($_ENV['REMOTE_API_URL'] ?? 'https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php');
    }

    public function getRates(Request $request, Response $response): Response
    {
        $body = (string) $request->getBody();
        $data = json_decode($body, true);

        if (!$data) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid JSON payload',
                'received' => $body
            ]));
            return $response->withHeader('Content-Type', self::JSON_CONTENT_TYPE)->withStatus(400);
        }

        $params = [
            'Unit Type ID' => $data['Unit Type ID'] ?? null,
            'Arrival'      => $data['Arrival'] ?? null,
            'Departure'    => $data['Departure'] ?? null,
            'Guests'       => $data['Guests'] ?? []
        ];

        try {
            $apiResponse = $this->client->post($this->apiUrl, ['json' => $params]);
            $rates = json_decode($apiResponse->getBody()->getContents(), true);

            $response->getBody()->write(json_encode($rates));
            return $response->withHeader('Content-Type', self::JSON_CONTENT_TYPE)->withStatus(200);
        } catch (GuzzleException $e) {
            error_log("RateController error: " . $e->getMessage());

            $response->getBody()->write(json_encode([
                'error' => 'Failed to fetch rates',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', self::JSON_CONTENT_TYPE)->withStatus(500);
        }
    }
}
