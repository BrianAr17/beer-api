<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\PaginationHelper;
use PDO;
use App\Helpers\Core\PDOService;
use Exception;

class BreweriesModel extends BaseModel
{
    public function __construct(private PDOService $pdo)
    {
        parent::__construct($pdo);
    }

    public function getBreweries(array $filters): array
    {

        $pdo_values = [];
        $sql = "SELECT * FROM breweries WHERE 1";

        return $this->paginate($sql, $pdo_values);
    }

    function getBreweryById(int $brewery_id): mixed
    {
        $sql = "SELECT * FROM breweries WHERE brewery_id = :brewery_id";
        $brewery = $this->fetchSingle(
            $sql,
            ["brewery_id" => $brewery_id]
        );
        return $brewery;
    }

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
