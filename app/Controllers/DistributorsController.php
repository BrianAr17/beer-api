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
     */
    public function __construct(private DistributorsModel $distributors_model) {}

    /**
     * Handle GET /distributors.
     *
     * Supports optional filters (name, region, contact_email, phone_number, founded_year,
     * license_number, warehouse_count, rating_avg), validated sorting (sort_by/order),
     * and pagination (page/page_size). Returns a JSON payload.
     *
     * @param Request  $request  Incoming HTTP request with query parameters.
     * @param Response $response HTTP response to write to.
     * @return Response JSON response containing the distributors list (paginated).
     *
     * @throws HttpInvalidStringException If a string filter contains invalid characters.
     * @throws HttpInvalidNumberException If a numeric filter is not a valid integer.
     * @throws HttpBadRequestException    If sort_by or order have invalid values.
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

        // Validate string filters
        foreach (['name', 'region', 'contact_email', 'phone_number', 'license_number'] as $field) {
            if (!empty($filters[$field]) && !ValidationHelper::isAlpha($filters[$field])) {
                throw new HttpInvalidStringException($request);
            }
        }

        // Validate numeric filters
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

        $response->getBody()->write(json_encode($distributors, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle GET /distributors/{distributor_id}.
     *
     * Returns a single distributor by its identifier as JSON.
     *
     * @param Request  $request  Incoming HTTP request.
     * @param Response $response HTTP response to write to.
     * @param array<string,int|string> $uri_args Route parameters; expects 'distributor_id'.
     * @return Response JSON response containing the distributor record.
     *
     * @throws HttpInvalidNumberException If distributor_id is not a positive integer.
     * @throws HttpNotFoundException      If the distributor does not exist.
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
