<?php
namespace Joao\ApiP\Services;

use GuzzleHttp\Client;

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
    }
}
