<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Models\DistributorsModel;
use App\Domain\Services\DistributorsService;
use App\Exceptions\HttpArrayNotFoundException;
use App\Exceptions\HttpBadRequestException;
use App\Exceptions\HttpInvalidNumberException;
use App\Exceptions\HttpInvalidStringException;
use App\Exceptions\HttpNotFoundException;
use App\Exceptions\HttpBodyNotFoundException;
use App\Validation\ValidationHelper;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller for Distributor resources.
 *
 * Validates inputs, applies pagination and sorting, delegates to the model,
 * and returns JSON responses for distributor collections and single records.
 */
class DistributorsController extends BaseController
{
    /**
     * Create a new DistributorsController.
     *
     * @param DistributorsModel $distributors_model Domain model used for data access.
     * @param DistributorsService $distributors_service Service handling business logic for distributors.
     */
    public function __construct(private DistributorsModel $distributors_model, private DistributorsService $distributors_service) {}

    /**
     * Handle GET /distributors.
     *
     * Supports optional filters (name, region, contact_email, phone_number, founded_year, license_number, warehouse_count, rating_avg),
     * validated sorting (sort_by/order), and pagination (page/page_size). Returns a JSON payload.
     *
     * @param Request  $request  Incoming HTTP request with query parameters.
     * @param Response $response HTTP response to write to.
     * @return Response JSON response containing the distributors list (paginated).
     *
     * @throws HttpInvalidStringException If a string filter contains invalid characters.
     * @throws HttpInvalidNumberException If a numeric filter is not a valid integer.
     * @throws HttpBadRequestException    If sort_by or order have invalid values.
     * @throws PDOException               On database-related errors.
     */
    public function handleGetDistributors(Request $request, Response $response): Response
    {
        $filters = $request->getQueryParams();

        $name = $filters['name'] ?? null;
        $region = $filters['region'] ?? null;
        $contact_email = $filters['contact_email'] ?? null;
        $phone_number = $filters['phone_number'] ?? null;
        $founded_year = $filters['founded_year'] ?? null;
        $license_number = $filters['license_number'] ?? null;
        $warehouse_count = $filters['warehouse_count'] ?? null;
        $rating_avg = $filters['rating_avg'] ?? null;

        // Validate string fields
        foreach (['name', 'region', 'contact_email', 'phone_number', 'license_number'] as $field) {
            if (!empty($filters[$field]) && !ValidationHelper::isAlpha($filters[$field])) {
                throw new HttpInvalidStringException($request);
            }
        }

        // Validate numeric fields
        foreach (['founded_year', 'warehouse_count', 'rating_avg'] as $field) {
            if (!empty($filters[$field]) && !ValidationHelper::isInt($filters[$field])) {
                throw new HttpInvalidNumberException($request);
            }
        }

        $sortBy = $filters['sort_by'] ?? 'distributor_id';
        $order  = strtolower($filters['order'] ?? 'asc');

        $validSortByFields = [
            'distributor_id',
            'name',
            'region',
            'contact_email',
            'phone_number',
            'founded_year',
            'license_number',
            'warehouse_count',
            'rating_avg'
        ];

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
            $this->distributors_model->setPaginationOptions((int)$page, (int)$pageSize);
        }

        $distributors = $this->distributors_model->getDistributors($filters);

        $payload = json_encode($distributors, JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle GET /distributors/{distributor_id}.
     *
     * Returns a single distributor by its identifier as JSON.
     *
     * @param Request  $request  Incoming HTTP request.
     * @param Response $response HTTP response to write to.
     * @param array<string, int|string> $uri_args Route parameters; expects 'distributor_id'.
     * @return Response JSON response containing the distributor record.
     *
     * @throws HttpInvalidNumberException If distributor_id is not a positive integer.
     * @throws HttpNotFoundException      If the distributor does not exist.
     * @throws PDOException               On database-related errors.
     */
    public function handleGetDistributorByID(Request $request, Response $response, array $uri_args): Response
    {
        $distributor_id = $uri_args['distributor_id'] ?? 0;

        if ($distributor_id <= 0 || !ValidationHelper::isInt($distributor_id)) {
            throw new HttpInvalidNumberException($request, 'Invalid Distributor ID: must be a positive integer');
        }

        $distributor = $this->distributors_model->getDistributorById((int)$distributor_id);

        if (!$distributor) {
            throw new HttpNotFoundException($request);
        }

        $response->getBody()->write(json_encode($distributor, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle POST /distributors.
     *
     * Creates a new distributor record.
     *
     * @param Request  $request  Incoming HTTP request containing JSON body.
     * @param Response $response HTTP response to write to.
     * @return Response JSON response indicating success or failure.
     *
     * @throws HttpBodyNotFoundException If the request body is missing.
     */
    public function handleCreateDistributor(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if ($data === null) {
        throw new HttpBodyNotFoundException($request);
    }

    // Ensure $data is a numeric array
        if (!isset($data[0])) {
        $data = [$data]; // wrap single object
    }

    $result = $this->distributors_service->doCreateDistributor($data);

    if ($result->isSuccess()) {
        return $this->renderJson($response, $result->getData(), 201);
    }

    $payload = [
        "status" => "error",
        "message" => "Failed to create the new distributor, refer to the details below",
        "details" => $result->getErrors()
    ];

    return $this->renderJson($response, $payload, 400);
    }


    /**
     * Handle PUT /distributors.
     *
     * Updates one or more distributor records.
     *
     * @param Request  $request  Incoming HTTP request containing JSON body.
     * @param Response $response HTTP response to write to.
     * @return Response JSON response indicating success or failure for each item.
     *
     * @throws HttpBodyNotFoundException  If the request body is missing.
     * @throws HttpNotFoundException      If the request contains no items.
     * @throws HttpArrayNotFoundException If an item is not an object.
     * @throws HttpInvalidNumberException If distributor_id is missing or invalid.
     */
    public function handleUpdateDistributor(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if ($data === null) {
            throw new HttpBodyNotFoundException($request);
        }

        $isList = is_array($data) && array_keys($data) === range(0, count($data) - 1);
        $items = $isList ? $data : [$data];

        if (empty($items)) {
            throw new HttpNotFoundException($request);
        }

        $results = [];
        $totalRows = 0;

        foreach ($items as $idx => $item) {
            if (!is_array($item)) {
                throw new HttpArrayNotFoundException($request, "Each item must be an object with fields.");
            }

            if (!isset($item['distributor_id'])) {
                throw new HttpInvalidNumberException($request, "Missing distributor_id");
            }

            $id = $item['distributor_id'];
            if (!is_numeric($id)) {
                $results[] = [
                    "index" => $idx,
                    "status" => "error",
                    "message" => "distributor_id must be numeric."
                ];
                continue;
            }

            $updateData = $item;
            unset($updateData['distributor_id']);

            if (empty($updateData)) {
                $results[] = [
                    "index" => $idx,
                    "distributor_id" => (int)$id,
                    "status" => "error",
                    "message" => "No fields to update for this item."
                ];
                continue;
            }

            $result = $this->distributors_service->doUpdateDistributor(
                $updateData,
                ['distributor_id' => (int)$id]
            );

            if ($result->isSuccess()) {
                $rows = (int)($result->getData()['rows_affected'] ?? 0);
                $totalRows += $rows;

                $results[] = [
                    "distributor_id" => (int)$id,
                    "status" => "ok"
                ];
            } else {
                $results[] = [
                    "distributor_id" => (int)$id,
                    "status" => "error",
                    "message" => "Update failed",
                    "details" => $result->getErrors()
                ];
            }
        }

        $payload = [
            "status" => "ok",
            "total_rows_affected" => $totalRows,
            "details" => $results
        ];

        return $this->renderJson($response, $payload, 200);
    }

    /**
     * Handle DELETE /distributors.
     *
     * Deletes one or more distributor records.
     *
     * @param Request  $request  Incoming HTTP request containing JSON body.
     * @param Response $response HTTP response to write to.
     * @return Response JSON response indicating success or failure.
     *
     * @throws HttpInvalidNumberException If no distributor_id is provided or invalid.
     */
    public function handleDeleteDistributor(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $ids = [];

        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $item) {
                if (isset($item['distributor_id'])) {
                    $ids[] = (int)$item['distributor_id'];
                }
            }
        } elseif (is_array($data)) {
            $ids = array_map('intval', $data);
        }

        if (empty($ids)) {
            throw new HttpInvalidNumberException($request, "No distributor_id provided");
        }

        $result = $this->distributors_service->doDeleteDistributor($ids);

        if ($result->isSuccess()) {
            return $this->renderJson($response, $result->getData(), 200);
        }

        $payload = [
            "status" => "error",
            "message" => "Failed to delete the distributor(s), refer to the details below",
            "details" => $result->getErrors()
        ];

        return $this->renderJson($response, $payload, 400);
    }
}
