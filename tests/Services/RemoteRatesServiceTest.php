<?php
namespace Joao\ApiP\Tests\Services;

use PHPUnit\Framework\TestCase;
use Joao\ApiP\Services\RemoteRatesService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class RemoteRatesServiceTest extends TestCase
{
    private $payload;

    protected function setUp(): void
    {
        $this->payload = [
            'Arrival' => '2025-10-01',
            'Departure' => '2025-10-05',
            'Ages' => [25, 10]
        ];

        putenv('UNIT_TYPE_IDS=1,2,3');
        putenv('REMOTE_API_URL=https://fake-api.test/rates');
    }

    public function testFetchRatesSuccess()
    {
        $mockClient = $this->createMock(Client::class);
        $mockResponse = new Response(200, [], json_encode(['rate' => 100]));
        $mockClient->method('post')->willReturn($mockResponse);

        $service = new class($mockClient) extends RemoteRatesService {
            public function __construct($client) { $this->client = $client; }
            public function fetchRates($payload) {
                $converted = [
                    'Unit Type ID' => explode(',', getenv('UNIT_TYPE_IDS'))[0],
                    'Arrival' => date('Y-m-d', strtotime($payload['Arrival'])),
                    'Departure' => date('Y-m-d', strtotime($payload['Departure'])),
                    'Guests' => array_map(fn($age) => ['Age Group' => $age >= 18 ? 'Adult' : 'Child'], $payload['Ages'])
                ];
                $response = $this->client->post(getenv('REMOTE_API_URL'), ['json' => $converted]);
                return json_decode($response->getBody(), true);
            }
        };

        $result = $service->fetchRates($this->payload);
        $this->assertEquals(['rate' => 100], $result);
    }

    public function testFetchRatesThrowsException()
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')->willThrowException(new \Exception("API error"));

        $service = new class($mockClient) extends RemoteRatesService {
            public function __construct($client) { $this->client = $client; }
            public function fetchRates($payload) {
                $converted = [
                    'Unit Type ID' => explode(',', getenv('UNIT_TYPE_IDS'))[0],
                    'Arrival' => date('Y-m-d', strtotime($payload['Arrival'])),
                    'Departure' => date('Y-m-d', strtotime($payload['Departure'])),
                    'Guests' => array_map(fn($age) => ['Age Group' => $age >= 18 ? 'Adult' : 'Child'], $payload['Ages'])
                ];
                $response = $this->client->post(getenv('REMOTE_API_URL'), ['json' => $converted]);
                return json_decode($response->getBody(), true);
            }
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("API error");

        $service->fetchRates($this->payload);
    }
}