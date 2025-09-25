<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Joao\ApiP\Controllers\RateController;

$app = AppFactory::create();

// Define a route for the frontend or API
$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("Welcome to the Gondwana Rates API");
    return $response;
});

// Example API route
$app->post('/api/rates', RateController::class . ':getRates');

$app->run();
