<?php

namespace App\Controllers;

use App\Domain\Models\BreweriesModel;
use App\Exceptions\HttpBadRequestException;
use App\Exceptions\HttpInvalidStringException;
use App\Exceptions\HttpNotFoundException;
use App\Exceptions\HttpInvalidNumberException;
use App\Validation\ValidationHelper;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller handling Brewery resources.
 *
 * Validates query/path parameters, supports pagination and sorting,
 * delegates data access to the model, and returns JSON responses.
 */
class BreweriesController extends BaseController
{
    /**
     * Create a new BreweriesController.
     *
     * @param BreweriesModel $breweries_model Domain model for brewery data access.
     */
    public function __construct(private BreweriesModel $breweries_model) {}

    /**
     * Handle GET /breweries
     *
     * Accepts optional filters:
     * - name, country, city, state, owner_name (strings)
     * - founded_year, employee_count (integers)
     *
     * Sorting:
     * - sort_by: one of brewery_id, name, brewery_type, city, state, country,
     *            website_url, founded_year, owner_name, rating_avg, employee_count
     * - order: asc|desc
     *
     * Pagination:
     * - page, page_size (integers)
     *
     * Returns JSON list (paginated) of breweries.
     *
     * @param Request  $request  Incoming HTTP request (query params used as filters).
     * @param Response $response HTTP response to populate.
     *
     * @return Response JSON response with breweries and pagination metadata.
     *
     * @throws HttpInvalidStringException If a string filter has invalid characters.
     * @throws HttpInvalidNumberException If a numeric filter is not a valid integer.
     * @throws HttpBadRequestException    If sort_by/order are invalid values.
     * @throws PDOException               On underlying database errors.
     */
    public function handleGetBreweries(Request $request, Response $response): Response
    {
        $filters = $request->getQueryParams();

        $name = $filters['name'] ?? null;
        $country = $filters['country'] ?? null;
        $city = $filters['city'] ??  null;
        $state = $filters['state'] ??  null;
        $owner_name = $filters['owner_name'] ??  null;
        $founded_year = $filters['founded_year'] ?? null;
        $employee_count = $filters['employee_count'] ?? null;

        if ($name !== null && !ValidationHelper::isAlpha($name)) {
            throw new HttpInvalidStringException($request);
        }

        if ($country !== null && !ValidationHelper::isAlpha($country)) {
            throw new HttpInvalidStringException($request);
        }

        if ($city !== null && !ValidationHelper::isAlpha($city)) {
            throw new HttpInvalidStringException($request);
        }

        if ($state !== null && !ValidationHelper::isAlpha($state)) {
            throw new HttpInvalidStringException($request);
        }

        if ($owner_name !== null && !ValidationHelper::isAlpha($owner_name)) {
            throw new HttpInvalidStringException($request);
        }

        if ($founded_year !== null && !ValidationHelper::isInt($founded_year)) {
            throw new HttpInvalidNumberException($request);
        }

        if ($employee_count !== null && !ValidationHelper::isInt($employee_count)) {
            throw new HttpInvalidNumberException($request);
        }

        $sortBy = $filters['sort_by'] ?? 'brewery_id';
        $order  = strtolower($filters['order'] ?? 'asc');

        $validSortByFields = ['brewery_id', 'name', 'brewery_type', 'city', 'state', 'country','website_url', 'founded_year', 'owner_name', 'rating_avg', 'employee_count'];
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
        if ($page !==null && $pageSize !== null) {
            $this->breweries_model->setPaginationOptions((int)$page, (int)$pageSize);
        }

        $breweries = $this->breweries_model->getBreweries($filters);

        // Step 5: Encode and return JSON
        $payload = json_encode($breweries, JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response->withHeader(HEADERS_CONTENT_TYPE, APP_MEDIA_TYPE_JSON);
    }

    /**
     * Handle GET /breweries/{brewery_id}
     *
     * Returns a single brewery by its ID in JSON format.
     *
     * @param Request  $request  Incoming HTTP request.
     * @param Response $response HTTP response to populate.
     * @param array    $uri_args Route arguments; expects ['brewery_id' => int].
     *
     * @return Response JSON response with the brewery record.
     *
     * @throws HttpInvalidNumberException If brewery_id is not a positive integer.
     * @throws HttpNotFoundException      If the brewery is not found.
     * @throws PDOException               On underlying database errors.
     */
   public function handleGetBreweriesByID(Request $request, Response $response, array $uri_args): Response
    {
        //* 1) Get the received ID from the URI.
        $brewery_id = $uri_args["brewery_id"];

        if ($brewery_id <= 0 || !ValidationHelper::isInt($brewery_id)) {
            throw new HttpInvalidNumberException($request, 'Invalid Brewery ID: must be a positive integer');
        }


        //* 2) Fetch the brewery info from the DB by ID.
        $brewery = $this->breweries_model->getBreweryById($brewery_id);


        if(!$brewery) {
            throw new HttpNotFoundException($request);
        }

        $response->getBody()->write(json_encode($brewery));
        return $response->withHeader('Content-Type', 'application/json');

    }
     /// End of the callback

}
