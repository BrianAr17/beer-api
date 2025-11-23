<?php

namespace App\Controllers;

use App\Domain\Models\BreweriesModel;
use App\Domain\Services\BreweriesService;
use App\Exceptions\HttpArrayNotFoundException;
use App\Exceptions\HttpBadRequestException;
use App\Exceptions\HttpInvalidStringException;
use App\Exceptions\HttpNotFoundException;
use App\Exceptions\HttpInvalidNumberException;
use App\Exceptions\HttpBodyNotFoundException;
use App\Validation\ValidationHelper;
use Frostybee\Valicomb\Validator;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller for Brewery resources.
 *
 * Validates inputs, applies pagination and sorting, delegates to the model,
 * and returns JSON responses for brewery collections and single records.
 */
class BreweriesController extends BaseController
{
    /**
     * Create a new BreweriesController.
     *
     * @param BreweriesModel $breweries_model Domain model used for data access.
     */
    public function __construct(private BreweriesModel $breweries_model, private BreweriesService $breweries_service) {}

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

        $validSortByFields = ['brewery_id', 'name', 'brewery_type', 'city', 'state', 'country', 'website_url', 'founded_year', 'owner_name', 'rating_avg', 'employee_count'];
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
    public function handleGetBreweriesByID(Request $request, Response $response, array $uri_args): Response
    {
        //* 1) Get the received ID from the URI.
        $brewery_id = $uri_args["brewery_id"];

        if ($brewery_id <= 0 || !ValidationHelper::isInt($brewery_id)) {
            throw new HttpInvalidNumberException($request, 'Invalid Brewery ID: must be a positive integer');
        }


        //* 2) Fetch the brewery info from the DB by ID.
        $brewery = $this->breweries_model->getBreweryById($brewery_id);


        if (!$brewery) {
            throw new HttpNotFoundException($request);
        }

        $response->getBody()->write(json_encode($brewery));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle POST /breweries.
     *
     * Creates a new brewery record using the BrewersService.
     * Expects a JSON body with the brewery fields. On success,
     * returns the created brewery data with HTTP 201.
     *
     * Example body:
     * {
     *   "name": "Some Brewery",
     *   "brewery_type": "micro",
     *   "city": "Montreal",
     *   "state": "Quebec",
     *   "country": "Canada",
     *   "owner_name": "John Doe",
     *   "founded_year": 1999,
     *   "employee_count": 42
     * }
     *
     * @param Request  $request  Incoming HTTP request containing the JSON payload.
     * @param Response $response HTTP response to write to.
     *
     * @return Response JSON response with created brewery data or error details.
     *
     * @throws HttpBodyNotFoundException If the request body is missing or not parseable.
     * @throws HttpInvalidStringException If a string field fails validation at the service layer.
     * @throws HttpInvalidNumberException If a numeric field fails validation at the service layer.
     * @throws HttpBadRequestException    For generic validation/format issues from the service layer.
     * @throws PDOException               On database-related errors.
     */
    public function handleCreateBrewery(Request $request, Response $response): Response
    {
        //* 1) Get the request payload (what the client sent embedded in the request body).
        $data = $request->getParsedBody();

        if ($data === null) {
            throw new HttpBodyNotFoundException($request);
        }

        $v = new Validator($data);
        $v->mapManyFieldsToRules([
            'name' => ['required', ['lengthMin', 5]],
            'brewery_type' => ['required', ['lengthMin', 5]],
            'city' => ['required', ['lengthBetween', 1, 25]],
            'state' => ['required', ['lengthBetween', 1, 2]],
            'country' => ['required', ['lengthBetween', 1, 60]],
            'website_url' => ['required', 'url'],
            'founded_year' => ['required', 'integer', ['min', 1000], ['max', (int) date('Y')]],
            'owner_name' => ['required', ['lengthBetween', 1, 50]],
            'rating_avg' => ['required', 'numeric', ['min', 0], ['max', 5]],
            'employee_count' => ['required', 'integer',['min', 1]]
        ]);

        if (!$v->validate()) {
            $payload = [
                'status' => 'error',
                'message' => 'The data that was inputed is invalid, enter proper data',
                'errors' => $v->errors(),
            ];

            return $this->renderJson($response, $payload, 400);
        }

        $data['founded_year'] = (int) $data['founded_year'];
        $data['rating_avg'] = (float) $data['rating_avg'];
        $data['employee_count'] = (int) $data['employee_count'];

        //dd($data);
        $result = $this->breweries_service->doCreateBrewery($data);
        if ($result->isSuccess()) {
            //! return a json response
            return $this->renderJson($response, $result->getData(), 201);
        }

        //* The operation failed, return an error response.
        $payload = [
            "status" => "error",
            "message" => "Failed to create the new brewery, refer to the details below",
            "details" => $result->getErrors()
        ];

        return $this->renderJson($response, $payload, 400);
    }

    /**
     * Handle PUT /breweries.
     *
     * Updates a single brewery record.
     *
     * The request body must be a JSON object containing:
     * - `brewery_id` (required): Integer > 0, identifying the brewery to update.
     * - Any subset of the updatable fields (all optional, but validated if present):
     *   - `name`
     *   - `brewery_type`
     *   - `city`
     *   - `state`
     *   - `country`
     *   - `website_url`
     *   - `founded_year`
     *   - `owner_name`
     *   - `rating_avg`
     *   - `employee_count`
     *
     * Example body:
     * {
     *   "brewery_id": 1,
     *   "name": "Updated Name",
     *   "rating_avg": 4.5
     * }
     *
     * Validation:
     * - Uses the Valicomb library to validate the request body.
     * - If validation fails, returns HTTP 400 with a JSON payload:
     *   {
     *     "status": "error",
     *     "message": "The data that was inputed is invalid, enter proper data",
     *     "errors": { ...field-specific errors... }
     *   }
     * - If no updatable fields (other than `brewery_id`) are provided, returns HTTP 400
     *   with an error indicating that there are no fields to update.
     *
     * Successful response:
     * - On success, returns HTTP 200 with a JSON payload similar to:
     *   {
     *     "status": "ok",
     *     "brewery_id": 1,
     *     "data": { ...service result data... }
     *   }
     *
     * Failure at service/DB level:
     * - If the update operation fails in the service or database layer,
     *   returns HTTP 400 with:
     *   {
     *     "status": "error",
     *     "brewery_id": 1,
     *     "message": "Update failed",
     *     "details": { ...service error details... }
     *   }
     *
     * @param Request  $request  Incoming HTTP request with JSON body.
     * @param Response $response HTTP response to write to.
     *
     * @return Response JSON response indicating success or failure of the update.
     *
     * @throws HttpBodyNotFoundException If the request body is missing or not parseable as an array.
     * @throws PDOException              On database-related errors thrown from the service/model layer.
     */
    public function handleUpdateBrewery(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if ($data === null || !is_array($data)) {
            throw new HttpBodyNotFoundException($request);
        }

        $v = new Validator($data);
        $v->mapManyFieldsToRules([
            'brewery_id'     => ['required', 'integer', ['min', 1]],
            'name'           => [['lengthMin', 5]],
            'brewery_type'   => [['lengthMin', 5]],
            'city'           => [['lengthBetween', 1, 25]],
            'state'          => [['lengthBetween', 1, 2]],
            'country'        => [['lengthBetween', 1, 60]],
            'website_url'    => ['url'],
            'founded_year'   => ['integer', ['min', 1000], ['max', (int) date('Y')]],
            'owner_name'     => [['lengthBetween', 1, 50]],
            'rating_avg'     => ['numeric', ['min', 0], ['max', 5]],
            'employee_count' => ['integer', ['min', 1]],
        ]);

        if (!$v->validate()) {
            $payload = [
                'status'  => 'error',
                'message' => 'The data that was inputed is invalid, enter proper data',
                'errors'  => $v->errors(),
            ];

            return $this->renderJson($response, $payload, 400);
        }

        $breweryId = (int) $data['brewery_id'];

        $updateData = $data;
        unset($updateData['brewery_id']);

        if (isset($updateData['founded_year'])) {
            $updateData['founded_year'] = (int) $updateData['founded_year'];
        }
        if (isset($updateData['rating_avg'])) {
            $updateData['rating_avg'] = (float) $updateData['rating_avg'];
        }
        if (isset($updateData['employee_count'])) {
            $updateData['employee_count'] = (int) $updateData['employee_count'];
        }

        if (empty($updateData)) {
            $payload = [
                'status'      => 'error',
                'brewery_id'  => $breweryId,
                'message'     => 'No fields to update for this brewery.',
            ];

            return $this->renderJson($response, $payload, 400);
        }

        $result = $this->breweries_service->doUpdateBrewery(
            $updateData,
            ['brewery_id' => $breweryId]
        );

        if ($result->isSuccess()) {
            $payload = [
                'status'      => 'ok',
                'brewery_id'  => $breweryId,
                'data'        => $result->getData(),
            ];

            return $this->renderJson($response, $payload, 200);
        }

        $payload = [
            'status'      => 'error',
            'brewery_id'  => $breweryId,
            'message'     => 'Update failed',
            'details'     => $result->getErrors(),
        ];

        return $this->renderJson($response, $payload, 400);
    }

    
    /**
     * Handle DELETE /breweries.
     *
     * Deletes one or multiple breweries by ID.
     *
     * Example body:
     * - A simple array of IDs:
     *   [1, 2, 3]
     * All collected IDs are passed to the service for deletion. If no valid IDs
     * are found, an HttpInvalidNumberException is thrown.
     *
     * @param Request  $request  Incoming HTTP request containing the IDs in the body.
     * @param Response $response HTTP response to write to.
     *
     * @return Response JSON response with deletion result or error details.
     *
     * @throws HttpInvalidNumberException If no brewery_id values are provided or IDs are invalid.
     * @throws PDOException              On database-related errors.
     */
    public function handleDeleteBrewery(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $ids = [];

        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $item) {
                if (isset($item['brewery_id'])) {
                    $ids[] = (int)$item['brewery_id'];
                }
            }
        } elseif (is_array($data)) {
            $ids = array_map('intval', $data);
        }

        if (empty($ids)) {
            throw new HttpInvalidNumberException($request, "No brewerey_Id provided");
        }

        $result = $this->breweries_service->doDeleteBrewery($ids);

        if ($result->isSuccess()) {
            //! return a json response
            return $this->renderJson($response, $result->getData(), 200);
        }

        $payload = [
            "status" => "error",
            "message" => "Failed to delete the new brewery, refer to the details below",
            "details" => $result->getErrors()
        ];
        return $this->renderJson($response, $payload, 400);
    }
    /// End of the callback
}
