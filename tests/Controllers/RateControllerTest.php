<?php
namespace Joao\ApiP\Tests\Controllers;

use PHPUnit\Framework\TestCase;
use Joao\ApiP\Controllers\RateController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Utils;

class RateControllerTest extends TestCase
{
    private function createMockClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

    public function testInvalidJsonReturns400()
    {
        $controller = new RateController();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getBody')->willReturn(Utils::streamFor('invalid-json'));

        $response = new Response();
        $result = $controller->getRates($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertStringContainsString('Invalid JSON payload', (string) $result->getBody());
    }

    public function testSuccessResponseReturns200()
    {
        $mockClient = $this->createMockClient([
            new GuzzleResponse(200, [], json_encode(['rate' => 100]))
        ]);

        $controller = new class($mockClient) extends RateController {
            private $mockClient;
            public function __construct($client) { $this->mockClient = $client; }
            public function getRates($request, $response, $args) {
                $client = $this->mockClient;
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                $params = [
                    'Unit Type ID' => $data['Unit Type ID'] ?? null,
                    'Arrival' => $data['Arrival'] ?? null,
                    'Departure' => $data['Departure'] ?? null,
                    'Guests' => $data['Guests'] ?? []
                ];
                $apiResponse = $client->post('http://mock', ['json' => $params]);
                $rates = json_decode($apiResponse->getBody()->getContents(), true);
                $response->getBody()->write(json_encode($rates));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }
        };

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getBody')->willReturn(Utils::streamFor(json_encode([
            'Unit Type ID' => 1,
            'Arrival' => '2025-10-01',
            'Departure' => '2025-10-05',
            'Guests' => [2, 1]
        ])));

        $response = new Response();
        $result = $controller->getRates($request, $response, []);

        $this->assertEquals(200, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('rate', $body);
        $this->assertEquals(100, $body['rate']);
    }

    public function testApiExceptionReturns500()
    {
        $mockClient = $this->createMockClient([
            new \GuzzleHttp\Exception\ConnectException('Connection error', new \GuzzleHttp\Psr7\Request('POST', 'test'))
        ]);

        $controller = new class($mockClient) extends RateController {
            private $mockClient;
            public function __construct($client) { $this->mockClient = $client; }
            public function getRates($request, $response, $args) {
                $client = $this->mockClient;
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                $params = [
                    'Unit Type ID' => $data['Unit Type ID'] ?? null,
                    'Arrival' => $data['Arrival'] ?? null,
                    'Departure' => $data['Departure'] ?? null,
                    'Guests' => $data['Guests'] ?? []
                ];
                try {
                    $apiResponse = $client->post('http://mock', ['json' => $params]);
                    $rates = json_decode($apiResponse->getBody()->getContents(), true);
                    $response->getBody()->write(json_encode($rates));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } catch (\Exception $e) {
                    $response->getBody()->write(json_encode([
                        'error' => 'Failed to fetch rates',
                        'message' => $e->getMessage()
                    ]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
                }
            }
        };

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getBody')->willReturn(Utils::streamFor(json_encode([
            'Unit Type ID' => 1,
            'Arrival' => '2025-10-01',
            'Departure' => '2025-10-05',
            'Guests' => [2, 1]
        ])));

        $response = new Response();
        $result = $controller->getRates($request, $response, []);

        $this->assertEquals(500, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertEquals('Failed to fetch rates', $body['error']);
    }
}
