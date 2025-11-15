<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Models\DistributorsModel;
use App\Helpers\Core\Result;
use App\Validation\Validator;

/**
 * Service class for Distributors resource.
 *
 * Handles create, update, and delete operations for distributor collection items.
 * Validates input using Valitron and communicates with the DistributorsModel.
 */
class DistributorsService extends BaseService
{
    /**
     * Validation rules for a distributor.
     *
     * @var array<string, array>
     */
    private array $distributor_rules = [
        'name' => ['required', ['lengthMin', 3]],
        'region' => ['required', ['lengthBetween', 1, 50]],
        'contact_email' => ['required', 'email'],
        'phone_number' => ['required', ['lengthBetween', 7, 20]],
        'founded_year' => ['required', 'integer', ['min', 1000], ['max', 2100]],
        'license_number' => ['required', ['lengthBetween', 1, 20]],
        'warehouse_count' => ['required', 'integer', ['min', 0]],
        'rating_avg' => ['required', 'numeric', ['min', 0], ['max', 5]],
    ];

    /**
     * Constructor for DistributorsService.
     *
     * @param DistributorsModel $distributors_model Model for interacting with the database table.
     */
    public function __construct(private DistributorsModel $distributors_model)
    {
    }

    /**
     * Create a new distributor record.
     *
     * @param array<int, array<string, mixed>> $new_distributors Array of new distributor data.
     * @return Result Result object containing success/failure information.
     */
    public function doCreateDistributor(array $new_distributors): Result
    {
        $data = $new_distributors[0] ?? [];
        $errors = [];

        $validation_result = $this->validateInput($data, $this->distributor_rules);
        if (is_array($validation_result)) {
            $errors = ["The inputs are invalid. Please provide correct inputs"];
            return Result::failure("Invalid input", $errors);
        }

        $last_inserted_id = $this->distributors_model->insertDistributor($data);

        if ($last_inserted_id) {
            return Result::success(
                "The new distributor was created successfully!",
                ["last_inserted_id" => $last_inserted_id]
            );
        }

        $errors = ["Failed to insert distributor"];
        return Result::failure("Creation failed", $errors);
    }

    /**
     * Update one or more distributor records.
     *
     * @param array<string, mixed> $update_distributor Data to update.
     * @param array<string, mixed> $updateWhere Conditions to match existing record(s).
     * @return Result Result object with update status.
     */
    public function doUpdateDistributor(array $update_distributor, array $updateWhere): Result
    {
        $errors = [];
        $validation_result = $this->validateInput($update_distributor, $this->distributor_rules);

        if (is_array($validation_result)) {
            $errors = ["The inputs are invalid. Please provide correct inputs"];
            return Result::failure("Invalid input", $errors);
        }

        $rows_affected = $this->distributors_model->updateDistributor($update_distributor, $updateWhere);

        if ($rows_affected) {
            return Result::success(
                "The distributor was updated successfully!",
                ["rows_affected" => $rows_affected]
            );
        }

        $errors = ["No rows were updated"];
        return Result::failure("Update failed", $errors);
    }

    /**
     * Delete one or more distributor records.
     *
     * @param array<int> $ids Array of distributor IDs to delete.
     * @return Result Result object containing deletion status.
     */
    public function doDeleteDistributor(array $ids): Result
    {
        $errors = [];
        $validation_result = $this->validateInput(
            ['distributor_id' => $ids[0] ?? null],
            ['distributor_id' => ['required', 'integer']]
        );

        if (is_array($validation_result)) {
            $errors = ["The inputs are invalid. Please provide correct inputs"];
            return Result::failure("Invalid input", $errors);
        }

        $rowsDeleted = 0;
        foreach ($ids as $id) {
            $rowsDeleted += $this->distributors_model->deleteDistributor(['distributor_id' => $id]);
        }

        if ($rowsDeleted > 0) {
            return Result::success(
                "The distributors were deleted successfully!",
                ["rows_deleted" => $rowsDeleted]
            );
        }

        $errors = ["No rows deleted (IDs may not exist)"];
        return Result::failure("Deletion failed", $errors);
    }
}
