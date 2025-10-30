<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Models\DistributorsModel;
use App\Exceptions\HttpBadRequestException;
use App\Exceptions\HttpInvalidNumberException;
use App\Exceptions\HttpInvalidStringException;
use App\Exceptions\HttpNotFoundException;
use App\Validation\ValidationHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller responsible for managing distributor-related API requests.
 *
 * Handles client requests, validates input data, manages pagination and sorting,
 * and communicates with the DistributorsModel for database access.
 */
class DistributorsController extends BaseController
{
    /**
     * Initialize the controller with a model dependency.
     *
     * @param DistributorsModel $distributors_model Model providing data access for distributors.
     */
    public function __construct(private DistributorsModel $distributors_model) {}

    /**
     * Process GET requests for the /distributors endpoint.
     *
     * Supports optional filters, sorting, and pagination.
     * Validates all inputs before retrieving data from the model.
     *
     * @param Request  $request  The HTTP request containing query parameters.
     * @param Response $response The HTTP response used to return JSON data.
     * @return Response JSON response with a list of distributors.
     *
     * @throws HttpInvalidStringException If a string filter contains invalid characters.
     * @throws HttpInvalidNumberException If a numeric filter contains an invalid number.
     * @throws HttpBadRequestException    If the sorting or order parameters are invalid.
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

        // Validate string-based fields
        foreach (['name', 'region', 'contact_email', 'phone_number', 'license_number'] as $field) {
            if (!empty($filters[$field]) && !ValidationHelper::isAlpha($filters[$field])) {
                throw new HttpInvalidStringException($request);
            }
        }

        // Validate numeric-based fields
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

        // Handle pagination
        $page = $filters['page'] ?? null;
        $pageSize = $filters['page_size'] ?? null;

        if ($page !== null && $pageSize !== null) {
            $this->distributors_model->setPaginationOptions((int)$page, (int)$pageSize);
        }

        $distributors = $this->distributors_model->getDistributors($filters);

        $response->getBody()->write(json_encode($distributors, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Process GET requests for the /distributors/{distributor_id} endpoint.
     *
     * Retrieves a single distributor record by its ID after validating input.
     *
     * @param Request  $request  The HTTP request containing the path parameter.
     * @param Response $response The HTTP response used to return JSON data.
     * @param array<string,int|string> $uri_args The URI arguments, must include distributor_id.
     * @return Response JSON response with a single distributor record.
     *
     * @throws HttpInvalidNumberException If distributor_id is not a valid positive integer.
     * @throws HttpNotFoundException      If no distributor is found for the provided ID.
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
}
