<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/db.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// $app = AppFactory::create();

// function handleRequest(Request $request, Response $response)
// {
//     $response->getBody()->write("Hello, Kemi");
//     return $response; 
// }


// $app->get(
//     '/',
//     function (Request $request, Response $response) {
//         return handleRequest($request, $response);
//     }

// );
$app = AppFactory::create();
$app->get('/test', function (Request $request, Response $response) {

    $response->getBody()->write("Hello, Kemisola");
        return $response;

});
// friends routes

require __DIR__ . '\public\routes\friends.php';


$app->run();
