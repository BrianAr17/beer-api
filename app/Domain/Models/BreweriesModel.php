<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\PaginationHelper;
use PDO;
use App\Helpers\Core\PDOService;
use Exception;

/**
 * Model class for handling all brewery-related database operations.
 *
 * Provides methods for retrieving, filtering, and sorting brewery data.
 * Uses PDO prepared statements and supports pagination and dynamic filtering.
 */

class BreweriesModel extends BaseModel
{
    public function __construct(private PDOService $pdo)
    {
        /**
         * Constructor for BreweriesModel.
         *
         * Initializes the database connection through the PDOService dependency.
         *
         * @param PDOService $pdo Database service instance used for executing queries.
         */
        parent::__construct($pdo);
    }

    /**
     * Retrieves a list of breweries with optional filters and sorting.
     *
     * Filters can include:
     * - name, brewery_type, city, state, country, website_url, founded_year,
     *   owner_name, rating_avg, employee_count
     *
     * Sort options are validated using an allowed list of columns.
     * Pagination is handled through the BaseModel's `paginate()` method.
     *
     * @param array $filters Associative array of filtering and sorting parameters.
     * @return array The resulting breweries data set as an array.
     *
     * @throws Exception If the query fails to execute.
     */
    public function getBreweries(array $filters): array
    {
        $pdo_values = [];
        $sql = "SELECT * FROM breweries WHERE 1";

        $name_filter = $filters["name"] ?? '';
        $brewery_type_filter = $filters["brewery_type"] ?? '';
        $city_filter = $filters["city"] ?? '';
        $state_filter = $filters["state"] ?? '';
        $country_filter = $filters["country"] ?? '';
        $website_url_filter = $filters["website_url"] ?? '';
        $founded_year_filter = $filters["founded_year"] ?? '';
        $owner_name_filter = $filters["owner_name"] ?? '';
        $rating_avg_filter = $filters["rating_avg"] ?? '';
        $employee_count_filter = $filters["employee_count"] ?? '';


        if (!empty($name_filter)) {
            $sql .= " AND name LIKE CONCAT('%', :name, '%') ";
            $pdo_values["name"] = $name_filter;
        }

        if (!empty($brewery_type_filter)) {
            $sql .= " AND brewery_type LIKE CONCAT('%', :brewery_type, '%') ";
            $pdo_values["brewery_type"] = $brewery_type_filter;
        }

        if (!empty($city_filter)) {
            $sql .= " AND city LIKE CONCAT('%', :city, '%') ";
            $pdo_values["city"] = $city_filter;
        }

        if (!empty($state_filter)) {
            $sql .= " AND state LIKE CONCAT('%', :state, '%') ";
            $pdo_values["state"] = $state_filter;
        }

        if (!empty($country_filter)) {
            $sql .= " AND country LIKE CONCAT('%', :country, '%') ";
            $pdo_values["country"] = $country_filter;
        }

        if (!empty($website_url_filter)) {
            $sql .= " AND website_url LIKE CONCAT('%', :website_url, '%') ";
            $pdo_values["website_url"] = $website_url_filter;
        }

        if (!empty($founded_year_filter)) {
            $sql .= " AND founded_year = :founded_year ";
            $pdo_values["founded_year"] = $founded_year_filter;
        }

        if (!empty($owner_name_filter)) {
            $sql .= " AND owner_name LIKE CONCAT('%', :owner_name, '%') ";
            $pdo_values["owner_name"] = $owner_name_filter;
        }

        if (!empty($rating_avg_filter)) {
            $sql .= " AND rating_avg >= :rating_avg ";
            $pdo_values["rating_avg"] = $rating_avg_filter;
        }

        if (!empty($employee_count_filter)) {
            $sql .= " AND employee_count >= :employee_count ";
            $pdo_values["employee_count"] = $employee_count_filter;
        }

        $allowedSort = [
            'brewery_id'     => 'brewery_id',
            'name'           => 'name',
            'brewery_type'   => 'brewery_type',
            'city'           => 'city',
            'state'          => 'state',
            'country'        => 'country',
            'website_url'    => 'website_url',
            'founded_year'   => 'founded_year',
            'owner_name'     => 'owner_name',
            'rating_avg'     => 'rating_avg',
            'employee_count' => 'employee_count',
        ];
        $defaultKey = 'name';

        $sql .= $this->buildOrderByFromSortParams(
            $filters, $allowedSort, $defaultKey
        );

        return $this->paginate($sql, $pdo_values);
    }

    /**
     * Retrieves a single brewery record by its ID.
     *
     * @param int $brewery_id The unique ID of the brewery.
     * @return mixed Returns the brewery data as an associative array, or null if not found.
     *
     * @throws Exception If the query fails to execute.
     */
    function getBreweryById(int $brewery_id): mixed
    {
        $sql = "SELECT * FROM breweries WHERE brewery_id = :brewery_id";
        $brewery = $this->fetchSingle(
            $sql,
            ["brewery_id" => $brewery_id]
        );
        return $brewery;
    }

    /**
     * Builds the ORDER BY clause dynamically based on filter input.
     *
     * Ensures that sorting only occurs on valid, allowed columns
     * to prevent SQL injection.
     *
     * @param array $filters The filters array containing sort parameters.
     * @param array $allowedMap A whitelist of sortable columns.
     * @param string $defaultKey The default column to sort by.
     * @return string A valid ORDER BY SQL clause.
     */
    private function buildOrderByFromSortParams(array $filters, array $allowedMap, string $defaultKey): string
    {
        $sortBy = isset($filters['sort_by']) ? trim((string)$filters['sort_by']) : $defaultKey;
        $order  = isset($filters['order'])   ? strtolower(trim((string)$filters['order'])) : 'asc';

        if (!isset($allowedMap[$sortBy])) {
            $sortBy = $defaultKey;
        }

        $dir = ($order === 'desc') ? 'DESC' : 'ASC';

        $columnSql = $allowedMap[$sortBy];
        return " ORDER BY {$columnSql} {$dir} ";
    }
}
