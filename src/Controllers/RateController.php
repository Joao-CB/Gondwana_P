<?php
namespace Joao\ApiP\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;

class RateController
{
    public function getRates(Request $request, Response $response, $args)
    {
        // Safely read JSON body
        $body = (string) $request->getBody();
        $data = json_decode($body, true);

        if (!$data) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid JSON payload',
                'received' => $body
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $client = new Client();
        $apiUrl = $_ENV['REMOTE_API_URL'] ?? 'https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php';

        // Ensure required params exist
        $params = [
            'Unit Type ID' => $data['Unit Type ID'] ?? null,
            'Arrival'      => $data['Arrival'] ?? null,
            'Departure'    => $data['Departure'] ?? null,
            'Guests'       => $data['Guests'] ?? []
        ];

        try {
            $apiResponse = $client->post($apiUrl, ['json' => $params]);

            $rates = json_decode($apiResponse->getBody()->getContents(), true);
            $response->getBody()->write(json_encode($rates));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            // Debug log (you can use Monolog or plain error_log)
            error_log("RateController error: " . $e->getMessage());

            $response->getBody()->write(json_encode([
                'error'   => 'Failed to fetch rates',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
