<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Models\LocationsModel;
use App\Exceptions\HttpBadRequestException;
use App\Exceptions\HttpNotFoundException;
use App\Exceptions\HttpValidationException;
use Slim\Exception\HttpSpecializedException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LocationsController extends BaseController
{
    public function __construct(private LocationsModel $locations_model) {}

    /**
     * GET /locations
     */
    public function handleGetLocations(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();

            // --- PAGINATION ---
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $pageSize = isset($params['page_size']) ? (int)$params['page_size'] : 10;
            if ($page < 1 || $pageSize < 1) {
                throw new HttpValidationException($request, "Page and page_size must be greater than zero");
            }
            $this->locations_model->setPaginationOptions($page, $pageSize);

            // --- FILTERING ---
            $filters = $params;
            unset($filters['page'], $filters['page_size'], $filters['sort_by'], $filters['order']);

            // --- GET LOCATIONS ---
            $result = $this->locations_model->getLocations($filters);

            return $this->renderJson($response, [
                'status' => 'success',
                'meta' => $result['meta'],
                'data' => $result['data']
            ], 200);

        } catch (HttpSpecializedException $e) {
            // Use toJson() if available, otherwise fallback
            $payload = method_exists($e, 'toJson') ? $e->toJson() : [
                'error' => [
                    'type' => get_class($e),
                    'message' => $e->getMessage()
                ]
            ];
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : ($e->getCode() ?: 400);
            return $this->renderJson($response, $payload, $statusCode);

        } catch (\Throwable $e) {
            return $this->renderJson($response, [
                'error' => [
                    'type' => 'InternalServerError',
                    'message' => 'Internal server error'
                ]
            ], 500);
        }
    }

    /**
     * GET /locations/{location_id}
     */
    public function handleGetLocationById(Request $request, Response $response, array $uriArgs): Response
    {
        try {
            $locationId = (int)($uriArgs['location_id'] ?? 0);
            if ($locationId <= 0) {
                throw new HttpBadRequestException($request, "Invalid location_id");
            }

            $location = $this->locations_model->getLocationById($locationId);
            if (!$location) {
                throw new HttpNotFoundException($request, "Location not found");
            }

            return $this->renderJson($response, $location, 200);

        } catch (HttpSpecializedException $e) {
            $payload = method_exists($e, 'toJson') ? $e->toJson() : [
                'error' => [
                    'type' => get_class($e),
                    'message' => $e->getMessage()
                ]
            ];
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : ($e->getCode() ?: 400);
            return $this->renderJson($response, $payload, $statusCode);

        } catch (\Throwable $e) {
            return $this->renderJson($response, [
                'error' => [
                    'type' => 'InternalServerError',
                    'message' => 'Internal server error'
                ]
            ], 500);
        }
    }
}
