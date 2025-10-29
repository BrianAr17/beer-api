<?php

namespace App\Domain\Services;

use App\Domain\Models\BreweriesModel;
use App\Helpers\Core\Result;

class BreweriesService extends BaseService
{
    public function __construct(private BreweriesModel $breweries_model) {

    }

    //* Implement at least 3 methods for creating, updating, deleting collection items.
    public function doCreateBrewery(array $new_breweries) : Result {
        //TODO: 1) USE THE Valitron library to validate the fields of the new collection to be processed (created, updated, or deleted).

        //* 2) Pass the collection item to the model.
        $last_inserted_id = $this->breweries_model->insertBrewery($new_breweries[0]);

        //* 3) Prepare the Result object to be returned.
        //? a) Return a successful operation
        return Result::success(
            "The new breweries were created successfully!",
            ["last_inserted_id" => $last_inserted_id]
        );

        //? b) Return a failure operation
        // $errors = ["Abey, brewery name must be provided"];
        // return Result::failure(
        //     "OH NO, NO GOOD",
        //     $errors
        // );
    }
}
