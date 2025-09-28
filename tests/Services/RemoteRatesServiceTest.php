<?php
namespace Joao\ApiP\Tests\Services;

use PHPUnit\Framework\TestCase;
use Joao\ApiP\Services\RemoteRatesService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;

class RemoteRatesServiceTest extends TestCase
{
    // Sample payload used in most tests
    private array $payload;

    /**
     * Setup method runs before each test.
     * Initializes payload and environment variables.
     */
    protected function setUp(): void
    {
        $this->payload = [
            'Arrival' => '2025-10-01',
            'Departure' => '2025-10-05',
            'Ages' => [25, 10]
        ];

        // Set mock environment variables for unit type IDs and API URL
        putenv('UNIT_TYPE_IDS=1,2,3');
        putenv('REMOTE_API_URL=https://fake-api.test/rates');
    }

    /**
     * Test that fetchRates returns the expected response
     * when the API responds successfully.
     */
    public function testFetchRatesSuccess(): void
    {
        // Mock Guzzle client to return a successful response
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')
            ->willReturn(new Response(200, [], json_encode(['rate' => 100])));

        $service = new RemoteRatesService($mockClient);
        $result = $service->fetchRates($this->payload);

        // Assert that the returned data matches the expected value
        $this->assertEquals(['rate' => 100], $result);
    }

    /**
     * Test that fetchRates throws a ConnectException
     * when the HTTP client fails to connect.
     */
    public function testFetchRatesThrowsException(): void
    {
        // Mock client to throw a connection exception
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')
            ->willThrowException(new ConnectException(
                'Connection error',
                new \GuzzleHttp\Psr7\Request('POST', 'test')
            ));

        $service = new RemoteRatesService($mockClient);

        // Expect the ConnectException with the correct message
        $this->expectException(ConnectException::class);
        $this->expectExceptionMessage('Connection error');

        $service->fetchRates($this->payload);
    }

    /**
     * Test that fetchRates throws an InvalidArgumentException
     * when no Unit Type IDs are provided in the environment.
     */
    public function testFetchRatesThrowsIfNoUnitTypeId(): void
    {
        // Clear the UNIT_TYPE_IDS environment variable
        putenv('UNIT_TYPE_IDS=');

        $mockClient = $this->createMock(Client::class);
        $service = new RemoteRatesService($mockClient);

        // Expect InvalidArgumentException with correct message
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No Unit Type ID provided in environment');

        $service->fetchRates($this->payload);
    }
}
