<?php

namespace App\Controllers;

use App\Domain\Models\BeerStylesModel;
use App\Domain\Services\BeerStylesService;
use App\Exceptions\HttpBadRequestException;
use App\Exceptions\HttpInvalidStringException;
use App\Exceptions\HttpNotFoundException;
use App\Exceptions\HttpInvalidNumberException;
use App\Exceptions\HttpBodyNotFoundException;
use App\Exceptions\HttpArrayNotFoundException;
use App\Validation\ValidationHelper;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller for Beer Style resources.
 *
 * Validates inputs, applies pagination and sorting, delegates to the model,
 * and returns JSON responses for beer style collections and single records.
 */
class BeerStylesController extends BaseController
{
    /**
     * Create a new BeerStylesController.
     *
     * @param BeerStylesModel $beerStyles_model Domain model used for data access.
     */
    public function __construct(private BeerStylesModel $beer_styles_model, private BeerStylesService $beer_styles_service) {}

    /**
     * Handle GET /beerStyles.
     *
     * Supports optional filters (name, country, city, state, owner_name, founded_year, employee_count),
     * validated sorting (sort_by/order), and pagination (page/page_size). Returns a JSON payload.
     *
     * @param Request  $request  Incoming HTTP request with query parameters.
     * @param Response $response HTTP response to write to.
     * @return Response JSON response containing the beerStyles list (paginated).
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

        $beerStyles = $this->beer_styles_model->getBeerStyles($filters);

        // Step 5: Encode and return JSON
        $payload = json_encode($beerStyles, JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response->withHeader(HEADERS_CONTENT_TYPE, APP_MEDIA_TYPE_JSON);
    }

    /**
     * Handle GET /beerStyles/{beerStyles_id}.
     *
     * Returns a single beerStyles by its identifier as JSON.
     *
     * @param Request  $request  Incoming HTTP request.
     * @param Response $response HTTP response to write to.
     * @param array<string, int|string> $uri_args Route parameters; expects 'beerStyles_id'.
     * @return Response JSON response containing the beer style record.
     *
     * @throws HttpInvalidNumberException If beerStyles_id is not a positive integer.
     * @throws HttpNotFoundException      If the beer style does not exist.
     * @throws PDOException               On database-related errors.
     */
    public function handleGetBeerStyleByID(Request $request, Response $response, array $uri_args): Response
    {
        //* 1) Get the received ID from the URI.
        $style_id = $uri_args["style_id"];

        if ($style_id <= 0 || !ValidationHelper::isInt($style_id)) {
            throw new HttpInvalidNumberException($request, 'Invalid Beer Style ID: must be a positive integer');
        }


        //* 2) Fetch the beer style info from the DB by ID.
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
    public function handleUpdateBeerStyle(Request $request, Response $response): Response
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

            if (!isset($item['beerStyle_id'])) {
                throw new HttpInvalidNumberException($request, "Missing beerStyle_id");
            }

            $beerStyleId = $item['beerStyle_id'];
            if (!is_numeric($beerStyleId)) {
                $results[] = [
                    "index" => $idx,
                    "status" => "error",
                    "message" => "beerStyle_id must be numeric."
                ];
                continue;
            }

            $updateData = $item;
            unset($updateData['beerStyle_id']);

            if (empty($updateData)) {
                $results[] = [
                    "index" => $idx,
                    "beerStyle_id" => (int)$beerStyleId,
                    "status" => "error",
                    "message" => "No fields to update for this item."
                ];
                continue;
            }

            $result = $this->beer_styles_service->doUpdateBeerStyle(
                $updateData,
                ['beerStyle_id' => (int)$beerStyleId]
            );

            if ($result->isSuccess()) {
                $rows = (int)($result->getData()['rows_affected'] ?? 0);
                $totalRows += $rows;

                $results[] = [
                    "beerStyle_id" => (int)$beerStyleId,
                    "status" => "ok",
                ];
            } else {
                $results[] = [
                    "beerStyle_id" => (int)$beerStyleId,
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


    public function handleDeleteBeerStyle(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $ids = [];

        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $item) {
                if (isset($item['beerStyle_id'])) {
                    $ids[] = (int)$item['beerStyle_id'];
                }
            }
        } elseif (is_array($data)) {
            $ids = array_map('intval', $data);
        }

        if (empty($ids)) {
            throw new HttpInvalidNumberException($request, "No beerStyle_Id provided");
        }

        $result = $this->beer_styles_service->doDeleteBeerStyles($ids);

        if ($result->isSuccess()) {
            //! return a json response
            return $this->renderJson($response, $result->getData(), 200);
        }

        $payload = [
            "status" => "error",
            "message" => "Failed to delete the new beer style, refer to the details below",
            "details" => $result->getErrors()
        ];
        return $this->renderJson($response, $payload, 400);
    }
    /// End of the callback

}
