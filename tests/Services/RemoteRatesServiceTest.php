<?php
namespace Joao\ApiP\Tests\Services;

use PHPUnit\Framework\TestCase;
use Joao\ApiP\Services\RemoteRatesService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;

class RemoteRatesServiceTest extends TestCase
{
    private array $payload;

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

    public function testFetchRatesSuccess(): void
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')
            ->willReturn(new Response(200, [], json_encode(['rate' => 100])));

        $service = new RemoteRatesService($mockClient);
        $result = $service->fetchRates($this->payload);

        $this->assertEquals(['rate' => 100], $result);
    }

    public function testFetchRatesThrowsException(): void
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')
            ->willThrowException(new ConnectException(
                'Connection error',
                new \GuzzleHttp\Psr7\Request('POST', 'test')
            ));

        $service = new RemoteRatesService($mockClient);

        $this->expectException(ConnectException::class);
        $this->expectExceptionMessage('Connection error');

        $service->fetchRates($this->payload);
    }

    public function testFetchRatesThrowsIfNoUnitTypeId(): void
    {
        putenv('UNIT_TYPE_IDS=');

        $mockClient = $this->createMock(Client::class);
        $service = new RemoteRatesService($mockClient);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No Unit Type ID provided in environment');

        $service->fetchRates($this->payload);
    }
}
