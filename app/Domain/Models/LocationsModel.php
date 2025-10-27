<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\Core\PDOService;

class LocationsModel extends BaseModel
{
    public function __construct(private PDOService $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Get all locations with optional filters, sorting, and pagination
     * Example query params:
     * ?city=Montreal&postal_code=H1A2B3&sort_by=name&order=desc
     */
    public function getLocations(array $filters = []): array
    {
        $pdo_values = [];
        $sql = "SELECT * FROM locations WHERE 1=1";

        // --- FILTERING ---
        $allowedFilters = ['name', 'city', 'postal_code', 'address'];
        foreach ($allowedFilters as $column) {
            if (!empty($filters[$column])) {
                $sql .= " AND $column = :$column";
                $pdo_values[":$column"] = $filters[$column];
            }
        }

        // --- SORTING ---
        $allowedSortColumns = [
            'id' => 'id',
            'name' => 'name',
            'city' => 'city',
            'postal_code' => 'postal_code'
        ];
        $sql .= $this->buildOrderByFromSortParams($filters, $allowedSortColumns, 'id');

        // --- PAGINATION handled by BaseModel ---
        return $this->paginate($sql, $pdo_values);
    }

    /**
     * Get a single location by ID
     */
    public function getLocationById(int $location_id): mixed
    {
        $sql = "SELECT * FROM locations WHERE id = :id";
        return $this->fetchSingle($sql, ["id" => $location_id]);
    }

    /**
     * Build the ORDER BY clause based on filters
     */
    private function buildOrderByFromSortParams(array $filters, array $allowedMap, string $defaultKey): string
    {
        $sortBy = isset($filters['sort_by']) ? trim((string)$filters['sort_by']) : $defaultKey;
        $order  = isset($filters['order']) ? strtolower(trim((string)$filters['order'])) : 'asc';

        if (!isset($allowedMap[$sortBy])) {
            $sortBy = $defaultKey;
        }

        $dir = ($order === 'desc') ? 'DESC' : 'ASC';
        $columnSql = $allowedMap[$sortBy];

        return " ORDER BY {$columnSql} {$dir} ";
    }
}
