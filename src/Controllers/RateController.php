<?php
namespace Joao\ApiP\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Joao\ApiP\Services\RemoteRatesService;

class RateController {
    public function getRates(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $service = new RemoteRatesService();
        $result = $service->fetchRates($data);

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
