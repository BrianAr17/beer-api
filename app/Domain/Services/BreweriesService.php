<?php

namespace App\Domain\Services;

use App\Domain\Models\BreweriesModel;
use App\Helpers\Core\Result;
use App\Validation\Validator;

class BreweriesService extends BaseService
{
    private $brewery_rules = array(
            'name' => array(
                'required',
                array('lengthMin', 5)
            ),
            'brewery_type' => array(
                'required',
                array('lengthMin', 5)
            ),
            'city' => array(
                'required',
                array('lengthBetween', 1, 25)
            ),
            'state' => [
                'required',
                ['lengthBetween', 1, 2]
            ],
            'country' => [
                'required',
                ['lengthBetween', 1, 60]
            ],
            'website_url' => [
                'required',
                'url'
            ],
            'founded_year' => [
                'required',
                'integer',
                ['min', 1000],
                ['max', 2025]
            ],
            'owner_name' => [
                'required',
                ['lengthBetween', 1, 50]
            ],
            'rating_avg' => [
                'required',
                'numeric',
                ['min', 0],
                ['max', 5]
            ],
            'employee_count' => [
                'required',
                'integer',
                ['min', 1]
            ],
        );

    public function __construct(private BreweriesModel $breweries_model) {

    }

    //* Implement at least 3 methods for creating, updating, deleting collection items.
    public function doCreateBrewery(array $new_breweries) : Result {
        //TODO: 1) USE THE Valitron library to validate the fields of the new collection to be processed (created, updated, or deleted).

        $errors = [];
        $validation_result = $this->validateInput($new_breweries[0], $this->brewery_rules);

        if (is_array($validation_result)) {
            $errors = ["The inputs are invalid. Please provide correct inputs"];
             return Result::failure(
                "Not good!",
                $errors
             );
        }

        //* 2) Pass the collection item to the model.
        $last_inserted_id = $this->breweries_model->insertBrewery($new_breweries[0]);

        //* 3) Prepare the Result object to be returned.
        //? a) Return a successful operation
        if ($last_inserted_id) {
            return Result::success(
                "The new breweries were created successfully!",
                ["last_inserted_id" => $last_inserted_id]
            );
        } else {
            //? b) Return a failure operation
            $errors = ["Abey, brewery name must be provided"];
            return Result::failure(
                "OH NO, NO GOOD",
                $errors
            );
        }
    }

    public function doUpdateBrewery(array $update_brewery, array $updateWhere) : Result {
        //TODO: 1) USE THE Valitron library to validate the fields of the new collection to be processed (created, updated, or deleted).

        $errors = [];
        $validation_result = $this->validateInput($update_brewery, $this->brewery_rules);

        if (is_array($validation_result)) {
            //! Invalid inputs.
            $errors = ["The inputs are invalid. Please provide correct inputs"];
             return Result::failure(
                "Not good!",
                $errors
             );
        }

        //* 2) Pass the collection item to the model.
        $rows_affected = $this->breweries_model->updateBrewery($update_brewery, $updateWhere);

        //* 3) Prepare the Result object to be returned.
        //? a) Return a successful operation
        if ($rows_affected) {
            return Result::success(
                "The new breweries were updated successfully!",
                ["rows_affected" => $rows_affected]
            );
        } else {
            //? b) Return a failure operation
            $errors = ["Abey, you need to input new data to update"];
            return Result::failure(
                "OH NO, NO GOOD",
                $errors
            );
        }
    }

    public function doDeleteBrewery(array $ids): Result
    {

       //TODO: 1) USE THE Valitron library to validate the fields of the new collection to be processed (created, updated, or deleted).

        $errors = [];
        $validation_result = $this->validateInput(
            ['brewery_id' => $ids[0] ?? null],
            ['brewery_id' => ['required', 'integer']]
        );

        if (is_array($validation_result)) {
            //! Invalid inputs.
            $errors = ["The inputs are invalid. Please provide correct inputs"];
            return Result::failure(
                "Not good!",
                $errors
            );
        }

        //* 2) Pass the collection item to the model.
        $rowsDeleted = 0;

        foreach ($ids as $id) {
            $rowsDeleted += $this->breweries_model->deleteBrewery(['brewery_id' => $id]);
        }

        //* 3) Prepare the Result object to be returned.
        //? a) Return a successful operation
        if ($rowsDeleted > 0) {
            return Result::success(
                "The breweries were deleted successfully!",
                ["rows_deleted" => $rowsDeleted]
            );
        } else {
            //? b) Return a failure operation
            $errors = ["No rows deleted (IDs may not exist, also make sure you input a number and not a character)"];
            return Result::failure(
                "OH NO, NO GOOD",
                $errors
            );
        }
    }
}
