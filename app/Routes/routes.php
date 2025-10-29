<?php

declare(strict_types=1);

use App\Controllers\AboutController;
use App\Controllers\BreweriesController;
use App\Controllers\LocationsController;
use App\Helpers\DateTimeHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


return static function (Slim\App $app): void {

    // Routes without authentication check: /login, /token

    //* ROUTE: GET /
    $app->get('/', [AboutController::class, 'handleAboutWebService']);


    //* ROUTE: GET /breweries
    $app->get('/breweries', [BreweriesController::class, 'handleGetBreweries']);

    //* ROUTE: GET /breweries/{brewery_id}
    $app->get('/breweries/{brewery_id}', [BreweriesController::class, 'handleGetBreweriesByID']);

     // GET /locations (Collection Resource)
    $app->get('/locations', [LocationsController::class, 'handleGetLocations']);

    //  GET /locations/{location_id}
    $app->get('/locations/{location_id}', [LocationsController::class, 'handleGetLocationByID']);

    //* ROUTE: GET /ping
    $app->get('/ping', function (Request $request, Response $response, $args) {

        $payload = [
            "greetings" => "Reporting! Hello there!",
            "now" => DateTimeHelper::now(DateTimeHelper::Y_M_D_H_M),
        ];
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR));
        return $response;
    });
    // Example route to test error handling.
    $app->get('/error', function (Request $request, Response $response, $args) {
        throw new \Slim\Exception\HttpNotFoundException($request, "Something went wrong");
    });
};
