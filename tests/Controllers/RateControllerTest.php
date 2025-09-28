<?php
namespace Joao\ApiP\Tests\Controllers;

use PHPUnit\Framework\TestCase;
use Joao\ApiP\Controllers\RateController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Utils;

class RateControllerTest extends TestCase
{
    /**
     * Create a mock Guzzle client that will return predefined responses.
     */
    private function createMockClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

    /**
     * Create an instance of RateController using the provided client.
     */
    private function createController(Client $client): RateController
    {
        return new RateController($client);
    }

    /**
     * Create a mock HTTP request with a JSON payload.
     */
    private function createRequest(array $payload): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getBody')->willReturn(Utils::streamFor(json_encode($payload)));
        return $request;
    }

    /**
     * Test that invalid JSON input returns a 400 Bad Request response.
     */
    public function testInvalidJsonReturns400()
    {
        $client = $this->createMockClient([]);
        $controller = $this->createController($client);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getBody')->willReturn(Utils::streamFor('invalid-json'));

        $response = new Response();
        $result = $controller->getRates($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertStringContainsString('Invalid JSON payload', (string)$result->getBody());
    }

    /**
     * Test that a valid payload returns a successful 200 response
     * and the expected 'rate' key in the body.
     */
    public function testSuccessResponseReturns200()
    {
        $mockClient = $this->createMockClient([
            new GuzzleResponse(200, [], json_encode(['rate' => 100]))
        ]);
        $controller = $this->createController($mockClient);

        $request = $this->createRequest([
            'Unit Type ID' => 1,
            'Arrival' => '2025-10-01',
            'Departure' => '2025-10-05',
            'Guests' => [2, 1]
        ]);

        $response = new Response();
        $result = $controller->getRates($request, $response, []);

        $this->assertEquals(200, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('rate', $body);
        $this->assertEquals(100, $body['rate']);
    }

    /**
     * Test that a Guzzle exception (e.g., connection error) results in a 500 response
     * with the expected error message returned.
     */
    public function testApiExceptionReturns500()
    {
        $mockClient = $this->createMockClient([
            new \GuzzleHttp\Exception\ConnectException(
                'Connection error',
                new \GuzzleHttp\Psr7\Request('POST', 'test')
            )
        ]);
        $controller = $this->createController($mockClient);

        $request = $this->createRequest([
            'Unit Type ID' => 1,
            'Arrival' => '2025-10-01',
            'Departure' => '2025-10-05',
            'Guests' => [2, 1]
        ]);

        $response = new Response();
        $result = $controller->getRates($request, $response, []);

        $this->assertEquals(500, $result->getStatusCode());
        $body = json_decode((string)$result->getBody(), true);
        $this->assertEquals('Failed to fetch rates', $body['error']);
        $this->assertEquals('Connection error', $body['message']);
    }
}
