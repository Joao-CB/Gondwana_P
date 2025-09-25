<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();

// Example route
$app->post('/api/rates', \Joao\ApiP\Controllers\RateController::class . ':getRates');

$app->run();
