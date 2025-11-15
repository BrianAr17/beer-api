<?php

namespace App\Domain\Services;

use App\Domain\Models\DistributorsModel;
use App\Helpers\Core\Result;

class DistributorsService extends BaseService
{
    public function __construct(private DistributorsModel $distributors_model) {}

    //* Implement at least 3 methods for creating, updating, deleting collection items.
    public function doCreateDistributor(array $new_distributors): Result
    {
        //TODO: 1) USE THE Valitron library to validate the fields of the new collection to be processed (created, updated, or deleted).

        //* 2) Pass the collection item to the model.
        foreach ($new_distributors as $key => $new_distributor) {
            $last_inserted_id = $this->distributors_model->insertDistributors($new_distributors[$key]);
        }

        //* 3) Prepare the Result object to be returned.
        //? a) Return a successful operation
        return Result::success(
            "The new distributors were created successfully!",
            ["last_inserted_id" => $last_inserted_id]
        );

        //? b) Return a failure operation
        // $errors = ["Distributor name must be provided"];
        // return Result::failure(
        //     "OH NO, NO GOOD",
        //     $errors
        // );
    }

    public function doUpdatingDistributors(array $new_distributors, array $updateWhere) {

        foreach($new_distributors as )



    }



    public function doDeletingDistributors(array array $delete_where) {



    }
}
