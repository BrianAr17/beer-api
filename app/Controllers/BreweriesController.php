<?php

namespace App\Controllers;

use App\Domain\Models\BreweriesModel;
use App\Exceptions\HttpBadRequestException;
use App\Exception\HttpNotFoundException;
use App\Exceptions\HttpInvalidNumberException;
use App\Exceptions\HttpRangeValidationException;
use App\Exceptions\HttpDateFormatException;
use App\Exceptions\HttpNotFoundException as ExceptionsHttpNotFoundException;
use InvalidArgumentException;
use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BreweriesController extends BaseController
{
    public function __construct(private BreweriesModel $breweries_model) {}

    public function handleGetBreweries(Request $request, Response $response): Response
    {
        $filters = $request->getQueryParams();

        $page = $filters['page'] ?? null;
        $pageSize = $filters['page_size'] ?? null;
        if ($page !==null & $pageSize !== null) {
            $this->breweries_model->setPaginationOptions((int)$page, (int)$pageSize);
        }


        $breweries = $this->breweries_model->getBreweries($filters);

        // Step 5: Encode and return JSON
        $payload = json_encode($breweries, JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response->withHeader(HEADERS_CONTENT_TYPE, APP_MEDIA_TYPE_JSON);
    }

    // ROUTE: GET /breweries/{brewery_id}
   public function handleGetBreweriesByID(Request $request, Response $response, array $uri_args): Response
    {
        //* 1) Get the received ID from the URI.
        $brewery_id = $uri_args["brewery_id"];

        if ($brewery_id <= 0) {
            throw new HttpInvalidNumberException($request, 'Invalid Brewery ID: must be a positive integer');
        }

        //* 2) Fetch the brewery info from the DB by ID.
        $brewery = $this->breweries_model->getBreweryById($brewery_id);

        //* 3) Prepare and return a JSON response.
        return $this->renderJson($response, $brewery);


        if(!$brewery) {
            throw new HttpNotFoundException($request);
        }

        if ($brewery === false) {

            //? Alternative PATH
            //? Option #2: Create a well-structured
            $payload = [
                "status" => "error",
                "code" => 404,
                "message" => "There was no record matching the supplied brewery id..."
            ];
            return $this->renderJson($response, $payload, 404);
        }

        //? Happy PATH:
        //return $this->renderJson($repsonse, $vendor);

        //! 4) What if the ID was invalid?
        //? Send a very-well structured JSON error response
    }
     /// End of the callback

}
