<?php

namespace App\Controllers;

use App\Domain\Models\BeerStylesModel;
use App\Domain\Services\BeerStylesService;
use App\Exceptions\HttpBadRequestException;
use App\Exceptions\HttpInvalidStringException;
use App\Exceptions\HttpNotFoundException;
use App\Exceptions\HttpInvalidNumberException;
use App\Validation\ValidationHelper;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller for Brewery resources.
 *
 * Validates inputs, applies pagination and sorting, delegates to the model,
 * and returns JSON responses for brewery collections and single records.
 */
class BeerStylesController extends BaseController
{
    /**
     * Create a new BreweriesController.
     *
     * @param BeerStylesModel $breweries_model Domain model used for data access.
     */
    public function __construct(private BeerStylesModel $beer_styles_model, private BeerStylesService $beer_styles_service) {}

    /**
     * Handle GET /breweries.
     *
     * Supports optional filters (name, country, city, state, owner_name, founded_year, employee_count),
     * validated sorting (sort_by/order), and pagination (page/page_size). Returns a JSON payload.
     *
     * @param Request  $request  Incoming HTTP request with query parameters.
     * @param Response $response HTTP response to write to.
     * @return Response JSON response containing the breweries list (paginated).
     *
     * @throws HttpInvalidStringException If a string filter contains invalid characters.
     * @throws HttpInvalidNumberException If a numeric filter is not a valid integer.
     * @throws HttpBadRequestException    If sort_by or order have invalid values.
     * @throws PDOException               On database-related errors.
     */
    public function handleGetBeerStyles(Request $request, Response $response): Response
    {
        $filters = $request->getQueryParams();

        $name = $filters['name'] ?? null;
        $description = $filters['description'] ?? null;
        $origin_country = $filters['origin_country'] ??  null;
        $color = $filters['color'] ??  null;
        $typical_abv_range = $filters['typical_abv_range'] ??  null;
        $glass_type = $filters['glass_type'] ?? null;
        $popularity_rank = $filters['popularity_rank'] ?? null;
        $pairing_foods = $filters['pairing_foods'] ?? null;


        if ($name !== null && !ValidationHelper::isAlpha($name)) {
            throw new HttpInvalidStringException($request);
        }

        if ($description !== null && !ValidationHelper::isAlpha($description)) {
            throw new HttpInvalidStringException($request);
        }

        if ($origin_country !== null && !ValidationHelper::isAlpha($origin_country)) {
            throw new HttpInvalidStringException($request);
        }

        if ($color !== null && !ValidationHelper::isAlpha($color)) {
            throw new HttpInvalidStringException($request);
        }

        if ($typical_abv_range !== null && !ValidationHelper::isAlpha($typical_abv_range)) {
            throw new HttpInvalidStringException($request);
        }

        if ($glass_type !== null && !ValidationHelper::isInt($glass_type)) {
            throw new HttpInvalidNumberException($request);
        }

        if ($popularity_rank !== null && !ValidationHelper::isInt($popularity_rank)) {
            throw new HttpInvalidNumberException($request);
        }
        if ($pairing_foods !== null && !ValidationHelper::isInt($pairing_foods)) {
            throw new HttpInvalidNumberException($request);
        }

        $sortBy = $filters['sort_by'] ?? 'style_id';
        $order  = strtolower($filters['order'] ?? 'asc');

        $validSortByFields = ['style_id', 'name', 'description', 'origin_country', 'color', 'typical_abv_range', 'glass_type', 'popularity_rank', 'pairing_foods'];
        $validOrders = ['asc', 'desc'];

        if (!in_array($sortBy, $validSortByFields)) {
            throw new HttpBadRequestException($request, "Invalid sort field: {$sortBy}");
        }
        if (!in_array($order, $validOrders)) {
            throw new HttpBadRequestException($request, "Invalid sort order: {$order}");
        }

        $filters['sort_by'] = $sortBy;
        $filters['order'] = $order;

        $page = $filters['page'] ?? null;
        $pageSize = $filters['page_size'] ?? null;
        if ($page !== null && $pageSize !== null) {
            $this->beer_styles_model->setPaginationOptions((int)$page, (int)$pageSize);
        }

        $breweries = $this->beer_styles_model->getBeerStyles($filters);

        // Step 5: Encode and return JSON
        $payload = json_encode($breweries, JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response->withHeader(HEADERS_CONTENT_TYPE, APP_MEDIA_TYPE_JSON);
    }

    /**
     * Handle GET /breweries/{brewery_id}.
     *
     * Returns a single brewery by its identifier as JSON.
     *
     * @param Request  $request  Incoming HTTP request.
     * @param Response $response HTTP response to write to.
     * @param array<string, int|string> $uri_args Route parameters; expects 'brewery_id'.
     * @return Response JSON response containing the brewery record.
     *
     * @throws HttpInvalidNumberException If brewery_id is not a positive integer.
     * @throws HttpNotFoundException      If the brewery does not exist.
     * @throws PDOException               On database-related errors.
     */
    public function handleGetBeerStyleByID(Request $request, Response $response, array $uri_args): Response
    {
        //* 1) Get the received ID from the URI.
        $style_id = $uri_args["style_id"];

        if ($style_id <= 0 || !ValidationHelper::isInt($style_id)) {
            throw new HttpInvalidNumberException($request, 'Invalid Brewery ID: must be a positive integer');
        }


        //* 2) Fetch the brewery info from the DB by ID.
        $beerStyle = $this->beer_styles_model->getBeerStyleById($style_id);


        if (!$beerStyle) {
            throw new HttpNotFoundException($request);
        }

        $response->getBody()->write(json_encode($beerStyle));
        return $response->withHeader('Content-Type', 'application/json');
    }

    //* POST /beer_styles
    public function handleCreateBeerStyle(Request $request, Response $response): Response
    {
        //* 1) Get the request payload (what the client sent embedded in the request body).
        $data = $request->getParsedBody();
        $result = $this->beer_styles_service->doCreateBeerStyle($data);
        if ($result->isSuccess()) {
            //! return a json response
            return $this->renderJson($response, $result->getData());
        }

        return $response;
    }
    /// End of the callback

}
