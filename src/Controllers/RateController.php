<?php
namespace Joao\ApiP\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;

class RateController
{
    public function getRates(Request $request, Response $response, $args)
    {
        $data = json_decode($request->getBody()->getContents(), true);

        $client = new Client();
        $apiUrl = $_ENV['REMOTE_API_URL'] ?? 'https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php';

        $params = [
            'Unit Type ID' => $data['Unit Type ID'],
            'Arrival' => $data['Arrival'],
            'Departure' => $data['Departure'],
            'Guests' => $data['Guests']
        ];

        try {
            $apiResponse = $client->request('POST', $apiUrl, [
                'json' => $params,
            ]);

            $rates = json_decode($apiResponse->getBody()->getContents(), true);
            $response->getBody()->write(json_encode($rates));
        } catch (\Exception $e) {
            // For debugging, you can log $e->getMessage() temporarily
            $response->getBody()->write(json_encode([
                'error' => 'Failed to fetch rates',
                'details' => $e->getMessage()
            ]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
