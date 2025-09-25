<?php
namespace Joao\ApiP\Services;

use GuzzleHttp\Client;

class RemoteRatesService {
    public function fetchRates($payload) {
        $client = new Client();
        $unitTypeIds = explode(',', getenv('UNIT_TYPE_IDS'));

        // Example: transform payload
        $converted = [
            'Unit Type ID' => $unitTypeIds[0],
            'Arrival' => date('Y-m-d', strtotime($payload['Arrival'])),
            'Departure' => date('Y-m-d', strtotime($payload['Departure'])),
            'Guests' => array_map(function($age) {
                return ['Age Group' => $age >= 18 ? 'Adult' : 'Child'];
            }, $payload['Ages'])
        ];

        $response = $client->post(getenv('REMOTE_API_URL'), ['json' => $converted]);
        return json_decode($response->getBody(), true);
    }
}
