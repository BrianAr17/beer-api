<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\PaginationHelper;
use PDO;
use App\Helpers\Core\PDOService;
use Exception;

/**
 * Model for beer styles data access.
 *
 * Provides filtered, sorted, and paginated reads from the `beer_styles` table.
 */
class BeerStylesModel extends BaseModel
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
     * Retrieve beer styles with optional filters, sorting, and pagination.
     *
     * Supported filters: name, description, origin_country, color, typical_abv_range, glass_type, popularity_rank, pairing_foods
     * Sorting is restricted
     * to a whitelist. Pagination is applied via BaseModel::paginate().
     *
     * @param array $filters Associative array of filter/sort/pagination options.
     * @return array Result set (may include pagination metadata depending on paginate()).
     *
     * @throws Exception On query failure.
     */
    public function getBeerStyles(array $filters): array
    {
        $pdo_values = [];
        $sql = "SELECT * FROM beer_styles WHERE 1";

        $name_filter = $filters["name"] ?? '';
        $description_filter = $filters["description"] ?? '';
        $origin_country_filter = $filters["origin_country"] ?? '';
        $color_filter = $filters["color"] ?? '';
        $typical_abv_range_filter = $filters["typical_abv_range"] ?? '';
        $glass_type_filter = $filters["glass_type"] ?? '';
        $popularity_rank_filter = $filters["popularity_rank"] ?? '';
        $pairing_foods_filter = $filters["pairing_foods"] ?? '';


        if (!empty($name_filter)) {
            $sql .= " AND name LIKE CONCAT('%', :name, '%') ";
            $pdo_values["name"] = $name_filter;
        }

        if (!empty($description_filter)) {
            $sql .= " AND description LIKE CONCAT('%', :description, '%') ";
            $pdo_values["description"] = $description_filter;
        }

        if (!empty($origin_country_filter)) {
            $sql .= " AND origin_country LIKE CONCAT('%', :origin_country, '%') ";
            $pdo_values["origin_country"] = $origin_country_filter;
        }

        if (!empty($color_filter)) {
            $sql .= " AND color LIKE CONCAT('%', :color, '%') ";
            $pdo_values["color"] = $color_filter;
        }

        if (!empty($typical_abv_range_filter)) {
            $sql .= " AND typical_abv_range LIKE CONCAT('%', :typical_abv_range, '%') ";
            $pdo_values["typical_abv_range"] = $typical_abv_range_filter;
        }

        if (!empty($glass_type_filter)) {
            $sql .= " AND glass_type LIKE CONCAT('%', :glass_type, '%') ";
            $pdo_values["glass_type"] = $glass_type_filter;
        }

        if (!empty($popularity_rank_filter)) {
            $sql .= " AND popularity_rank = :popularity_rank ";
            $pdo_values["popularity_rank"] = $popularity_rank_filter;
        }

        if (!empty($pairing_foods_filter)) {
            $sql .= " AND pairing_foods LIKE CONCAT('%', :pairing_foods, '%') ";
            $pdo_values["pairing_foods"] = $pairing_foods_filter;
        }

        $allowedSort = [
            'style_id'     => 'style_id',
            'name'           => 'name',
            'description'   => 'description',
            'origin_country'           => 'origin_country',
            'color'          => 'color',
            'typical_abv_range'        => 'typical_abv_range',
            'glass_type'    => 'glass_type',
            'popularity_rank'   => 'popularity_rank',
            'pairing_foods'     => 'pairing_foods',
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
     * Retrieve a beer style by its identifier.
     *
     * Returns the row as an associative array or null when not found.
     *
     * @param int $style_id Primary key of the beer style.
     * @return array|null Beer Style row or null if missing.
     *
     * @throws Exception On query failure.
     */
    function getBeerStyleById(int $style_id): mixed
    {
        $sql = "SELECT * FROM beer_styles WHERE style_id = :style_id";
        $style = $this->fetchSingle(
            $sql,
            ["style_id" => $style_id]
        );
        return $style;
    }

    //! Create function for beer styles services
    //* For iteration #2
    public function insertBeerStyle(array $new_beer_style)
    {
        return $this->insert("beer_styles", $new_beer_style);
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
