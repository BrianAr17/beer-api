<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\Core\PDOService;
use Exception;

/**
 * Model for distributor data access.
 *
 * Provides filtered, sorted, and paginated reads from the `distributors` table.
 */
class DistributorsModel extends BaseModel
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
     * Retrieve distributors with optional filters, sorting, and pagination.
     *
     * Supported filters:
     * - distributor_id, name, region, contact_email, phone_number, founded_year,
     *   license_number, warehouse_count, rating_avg.
     * Sorting is restricted to a whitelist. Pagination is applied via BaseModel::paginate().
     *
     * @param array $filters Associative array of filter, sort, and pagination options.
     * @return array Result set (may include pagination metadata depending on paginate()).
     * @throws Exception On query failure.
     */
    public function getDistributors(array $filters): array
    {
        $pdo_values = [];
        $sql = "SELECT * FROM distributors WHERE 1";

        $distributor_id_filter = $filters["distributor_id"] ?? '';
        $name_filter           = $filters["name"] ?? '';
        $region_filter         = $filters["region"] ?? '';
        $contact_email_filter  = $filters["contact_email"] ?? '';
        $phone_number_filter   = $filters["phone_number"] ?? '';
        $founded_year_filter   = $filters["founded_year"] ?? '';
        $license_number_filter = $filters["license_number"] ?? '';
        $warehouse_count_filter = $filters["warehouse_count"] ?? '';
        $rating_avg_filter     = $filters["rating_avg"] ?? '';

        if (!empty($distributor_id_filter)) {
            $sql .= " AND distributor_id = :distributor_id";
            $pdo_values["distributor_id"] = $distributor_id_filter;
        }
        if (!empty($name_filter)) {
            $sql .= " AND name LIKE CONCAT('%', :name, '%')";
            $pdo_values["name"] = $name_filter;
        }
        if (!empty($region_filter)) {
            $sql .= " AND region LIKE CONCAT('%', :region, '%')";
            $pdo_values["region"] = $region_filter;
        }
        if (!empty($contact_email_filter)) {
            $sql .= " AND contact_email LIKE CONCAT('%', :contact_email, '%')";
            $pdo_values["contact_email"] = $contact_email_filter;
        }
        if (!empty($phone_number_filter)) {
            $sql .= " AND phone_number LIKE CONCAT('%', :phone_number, '%')";
            $pdo_values["phone_number"] = $phone_number_filter;
        }
        if (!empty($founded_year_filter)) {
            $sql .= " AND founded_year = :founded_year";
            $pdo_values["founded_year"] = $founded_year_filter;
        }
        if (!empty($license_number_filter)) {
            $sql .= " AND license_number LIKE CONCAT('%', :license_number, '%')";
            $pdo_values["license_number"] = $license_number_filter;
        }
        if (!empty($warehouse_count_filter)) {
            $sql .= " AND warehouse_count >= :warehouse_count";
            $pdo_values["warehouse_count"] = $warehouse_count_filter;
        }
        if (!empty($rating_avg_filter)) {
            $sql .= " AND rating_avg >= :rating_avg";
            $pdo_values["rating_avg"] = $rating_avg_filter;
        }

        $allowedSort = [
            'distributor_id' => 'distributor_id',
            'name'           => 'name',
            'region'         => 'region',
            'contact_email'  => 'contact_email',
            'phone_number'   => 'phone_number',
            'founded_year'   => 'founded_year',
            'license_number' => 'license_number',
            'warehouse_count' => 'warehouse_count',
            'rating_avg'     => 'rating_avg',
        ];
        $defaultKey = 'name';

        $sql .= $this->buildOrderByFromSortParams($filters, $allowedSort, $defaultKey);

        return $this->paginate($sql, $pdo_values);
    }

    /**
     * Retrieve a distributor by its identifier.
     *
     * Returns the row as an associative array or null when not found.
     *
     * @param int $distributor_id Primary key of the distributor.
     * @return array|null Distributor row or null if missing.
     * @throws Exception On query failure.
     */
    public function getDistributorById(int $distributor_id): mixed
    {
        $sql = "SELECT * FROM distributors WHERE distributor_id = :distributor_id";
        return $this->fetchSingle($sql, ["distributor_id" => $distributor_id]);
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
        $sortBy = $filters['sort_by'] ?? $defaultKey;
        $order  = strtolower($filters['order'] ?? 'asc');

        if (!isset($allowedMap[$sortBy])) {
            $sortBy = $defaultKey;
        }

        $dir = ($order === 'desc') ? 'DESC' : 'ASC';
        return " ORDER BY {$allowedMap[$sortBy]} {$dir} ";
    }
}
