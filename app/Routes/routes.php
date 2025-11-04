<?php

declare(strict_types=1);

use App\Controllers\AboutController;
use App\Controllers\BreweriesController;
use App\Controllers\BeerStylesController;
use App\Controllers\DistributorsController;
use App\Helpers\DateTimeHelper;
use App\Middleware\ContentNegotiationMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


return static function (Slim\App $app): void {

    // Routes without authentication check: /login, /token

    //* ROUTE: GET /
    $app->get('/', [AboutController::class, 'handleAboutWebService']);

    //! Breweries
    //* ROUTE: GET /breweries
    $app->get('/breweries', [BreweriesController::class, 'handleGetBreweries']);

    //* ROUTE: POST /breweries
    $app->post('/breweries', [BreweriesController::class, 'handleCreateBrewery']);

    //* ROUTE: PUT /breweries
    $app->put('/breweries', [BreweriesController::class, "handleUpdateBrewery"]);

    //* ROUTE: DELETE /breweries
    $app->delete('/breweries', [BreweriesController::class, "handleDeleteBrewery"]);

    //* ROUTE: GET /breweries/{brewery_id}
    $app->get('/breweries/{brewery_id}', [BreweriesController::class, 'handleGetBreweriesByID']);

    //! Distributors
    //* ROUTE: Distributors
    // GET all distributors
    $app->get('/distributors', [DistributorsController::class, 'handleGetDistributors']);

    // GET distributor by ID
    $app->get('/distributors/{distributor_id}', [DistributorsController::class, 'handleGetDistributorByID']);

    //! Beer Styles
    //* ROUTE: GET /beer-styles
    $app->get('/beer-styles', [BeerStylesController::class, "handleGetBeerStyles"]);

    //* ROUTE: GET /beer-styles/{style_id}
    $app->get('/beer-styles/{style_id}', [BeerStylesController::class, 'handleGetBeerStyleByID']);

    //* ROUTE: POST /beer-styles
    $app->post('/beer-styles', [BeerStylesController::class, "handleCreateBeerStyle"]);

    //! PING
    //* ROUTE: GET /ping
    $app->get('/ping', function (Request $request, Response $response, $args) {

        $payload = [
            "greetings" => "Reporting! Hello there!",
            "now" => DateTimeHelper::now(DateTimeHelper::Y_M_D_H_M),
        ];
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR));
        return $response;
    });

    //! Adding Middleware
    $app->add(new ContentNegotiationMiddleware());

    // Example route to test error handling.
    $app->get('/error', function (Request $request, Response $response, $args) {
        throw new \Slim\Exception\HttpNotFoundException($request, "Something went wrong");
    });
};
