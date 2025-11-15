<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\PaginationHelper;
use PDO;
use App\Helpers\Core\PDOService;
use Exception;

/**
 * Model for brewery data access.
 *
 * Provides filtered, sorted, and paginated reads from the `breweries` table.
 */
class BreweriesModel extends BaseModel
{
    /**
     * Construct the model with a PDO service.
     *
     * @param PDOService $pdo Database service used to execute queries.
     */
    public function __construct(private PDOService $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Retrieve breweries with optional filters, sorting, and pagination.
     *
     * Supported filters: name, brewery_type, city, state, country, website_url,
     * founded_year, owner_name, rating_avg, employee_count. Sorting is restricted
     * to a whitelist. Pagination is applied via BaseModel::paginate().
     *
     * @param array $filters Associative array of filter/sort/pagination options.
     * @return array Result set (may include pagination metadata depending on paginate()).
     *
     * @throws Exception On query failure.
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
            $filters,
            $allowedSort,
            $defaultKey
        );

        return $this->paginate($sql, $pdo_values);
    }

    /**
     * Retrieve a brewery by its identifier.
     *
     * Returns the row as an associative array or null when not found.
     *
     * @param int $brewery_id Primary key of the brewery.
     * @return array|null Brewery row or null if missing.
     *
     * @throws Exception On query failure.
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

    //!Create function for breweries services
    //* For iteration #2
    public function insertBrewery(array $new_brewery)
    {
        return $this->insert("breweries", $new_brewery);
        //TODO: READ THE DOC, you have examples of their usage.
    }

    public function updateBrewery(array $update_brewery, array $updateWhere)
    {
        return $this->update("breweries", $update_brewery, $updateWhere);
        //TODO: READ THE DOC, you have examples of their usage.
    }

    public function deleteBrewery(array $delete_where)
    {
        return $this->delete("breweries", $delete_where);
        //TODO: READ THE DOC, you have examples of their usage.
    }

    /**
     * Build an ORDER BY clause from user input safely.
     *
     * Validates the requested column and direction against a whitelist and
     * falls back to a default column when needed.
     *
     * @param array  $filters    Filter array containing 'sort_by' and 'order'.
     * @param array  $allowedMap Map of allowed sort keys to SQL column names.
     * @param string $defaultKey Default sort key if input is invalid.
     * @return string ORDER BY fragment beginning with a space.
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
