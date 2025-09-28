<?php
namespace Joao\ApiP\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class RateController
{
    // HTTP client used to call the remote Gondwana API
    private Client $client;

    // Remote API base URL
    private string $apiUrl;

    // Content type for JSON responses
    private const JSON_CONTENT_TYPE = 'application/json';

    public function __construct(Client $client = null, ?string $apiUrl = null)
    {
        // Use injected client or create a new Guzzle client if none provided
        $this->client = $client ?? new Client();

        // Use injected API URL, or fall back to environment variable, or default to Gondwana test endpoint
        $this->apiUrl = $apiUrl ?? ($_ENV['REMOTE_API_URL'] ?? 'https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php');
    }

    /**
     * Handle a request to fetch rates from the remote Gondwana API.
     * 
     * @param Request $request  Incoming HTTP request (contains JSON payload)
     * @param Response $response Outgoing HTTP response
     * @return Response
     */
    public function getRates(Request $request, Response $response): Response
    {
        // Read and decode the JSON request body
        $body = (string) $request->getBody();
        $data = json_decode($body, true);

        // If JSON is invalid, return a 400 error
        if (!$data) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid JSON payload',
                'received' => $body
            ]));
            return $response->withHeader('Content-Type', self::JSON_CONTENT_TYPE)->withStatus(400);
        }

        // Build parameters to send to the remote API
        $params = [
            'Unit Type ID' => $data['Unit Type ID'] ?? null,
            'Arrival'      => $data['Arrival'] ?? null,
            'Departure'    => $data['Departure'] ?? null,
            'Guests'       => $data['Guests'] ?? []
        ];

        try {
            // Call the remote API with the given parameters
            $apiResponse = $this->client->post($this->apiUrl, ['json' => $params]);

            // Decode the remote API response
            $rates = json_decode($apiResponse->getBody()->getContents(), true);

            // Write the response body with the API result and return 200 OK
            $response->getBody()->write(json_encode($rates));
            return $response->withHeader('Content-Type', self::JSON_CONTENT_TYPE)->withStatus(200);

        } catch (GuzzleException $e) {
            // Log the error server-side for debugging
            error_log("RateController error: " . $e->getMessage());

            // Return a 500 error to the client with error message
            $response->getBody()->write(json_encode([
                'error' => 'Failed to fetch rates',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', self::JSON_CONTENT_TYPE)->withStatus(500);
        }
    }
}
