<?php
namespace Joao\ApiP\Services;

use GuzzleHttp\Client;
<<<<<<< HEAD

class RemoteRatesService {
    public function fetchRates($payload) {
        // Create HTTP client
        $client = new Client();

        // Read unit type IDs from environment (comma-separated string → array)
        $unitTypeIds = explode(',', getenv('UNIT_TYPE_IDS'));

        // Build request payload for remote API
        $converted = [
            'Unit Type ID' => $unitTypeIds[0], // use first ID by default
            'Arrival' => date('Y-m-d', strtotime($payload['Arrival'])), // format dates
            'Departure' => date('Y-m-d', strtotime($payload['Departure'])),
            'Guests' => array_map(function($age) {
                // Classify guests as Adult or Child by age
                return ['Age Group' => $age >= 18 ? 'Adult' : 'Child'];
            }, $payload['Ages'])
        ];

        // Send POST request to remote API with JSON body
        $response = $client->post(getenv('REMOTE_API_URL'), ['json' => $converted]);

        // Decode and return JSON response
        return json_decode($response->getBody(), true);
=======
use GuzzleHttp\Exception\GuzzleException;

class RemoteRatesService
{
    private Client $client;

    public function __construct(Client $client = null)
    {
        // Use injected client for testing, or default to a new Client
        $this->client = $client ?? new Client();
    }

    /**
     * Fetch rates from the remote API
     *
     * @param array $payload ['Arrival' => string, 'Departure' => string, 'Ages' => int[]]
     * @return array
     * @throws GuzzleException
     */
    public function fetchRates(array $payload): array
    {
        // Read unit type IDs from environment
        $unitTypeIds = explode(',', getenv('UNIT_TYPE_IDS') ?: '');

        if (empty($unitTypeIds[0])) {
            throw new \InvalidArgumentException('No Unit Type ID provided in environment');
        }

        // Prepare API payload
        $converted = [
            'Unit Type ID' => $unitTypeIds[0],
            'Arrival' => date('Y-m-d', strtotime($payload['Arrival'] ?? '')),
            'Departure' => date('Y-m-d', strtotime($payload['Departure'] ?? '')),
            'Guests' => array_map(fn($age) => ['Age Group' => $age >= 18 ? 'Adult' : 'Child'], $payload['Ages'] ?? [])
        ];

        // Send POST request and decode JSON response
        $response = $this->client->post(getenv('REMOTE_API_URL'), ['json' => $converted]);
        return json_decode($response->getBody()->getContents(), true);
>>>>>>> e2f7f69 (Add SonarCloud config, QA workflow, test cases, and license fix)
    }
}
