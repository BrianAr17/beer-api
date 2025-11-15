<?php

namespace App\Domain\Services;

use App\Domain\Models\BeerStylesModel;
use App\Helpers\Core\Result;
use App\Validation\Validator;

class BeerStylesService extends BaseService
{
    private $beerStyle_rules = array(
        'name' => array(
            'required',
            ['lengthMin', 3],
            ['lengthMax', 100]
        ),
        'description' => array(
            'required',
            ['lengthMin', 10],
            ['lengthMax', 500]
        ),
        'origin_country' => array(
            'required',
            ['lengthBetween', 2, 50]
        ),
        'color' => [
            'required',
            ['lengthBetween', 1, 20]
        ],
        'typical_abv_range' => [
            'required',
            ['lengthBetween', 1, 50]
        ],
        'glass_type' => [
            'required',
            ['lengthBetween', 2, 50]
        ],
        'popularity_rank' => [
            'required',
            'integer',
            ['min', 1],
            ['max', 10000]
        ],
        'pairing_foods' => [
            'required',
            ['lengthMin', 1],
            ['lengthMax', 100]
        ],
    );



    public function __construct(private BeerStylesModel $beerStyles_model) {}

    //* Implement at least 3 methods for creating, updating, deleting collection items.
    public function doCreateBeerStyle(array $new_beerStyles): Result
    {
        //TODO: 1) USE THE Valitron library to validate the fields of the new collection to be processed (created, updated, or deleted).

        $errors = [];
        $validation_result = $this->validateInput($new_beerStyles[0], $this->beerStyle_rules);

        if (is_array($validation_result)) {
            $errors = ["The inputs are invalid. Please provide correct inputs"];
            return Result::failure(
                "Not good!",
                $errors
            );
        }

        //* 2) Pass the collection item to the model.
        $last_inserted_id = $this->beerStyles_model->insertBeerStyle($new_beerStyles[0]);

        //* 3) Prepare the Result object to be returned.
        //? a) Return a successful operation
        if ($last_inserted_id) {
            return Result::success(
                "The new beer styles were created successfully!",
                ["last_inserted_id" => $last_inserted_id]
            );
        } else {
            //? b) Return a failure operation
            $errors = ["Abey, beer style name must be provided"];
            return Result::failure(
                "OH NO, NO GOOD",
                $errors
            );
        }
    }

    public function doUpdateBeerStyle(array $update_beerStyle, array $updateWhere): Result
    {
        //TODO: 1) USE THE Valitron library to validate the fields of the new collection to be processed (created, updated, or deleted).

        $errors = [];
        $validation_result = $this->validateInput($update_beerStyle, $this->beerStyle_rules);

        if (is_array($validation_result)) {
            //! Invalid inputs.
            $errors = ["The inputs are invalid. Please provide correct inputs"];
            return Result::failure(
                "Not good!",
                $errors
            );
        }

        //* 2) Pass the collection item to the model.
        $rows_affected = $this->beerStyles_model->updateBeerStyle($update_beerStyle, $updateWhere);

        //* 3) Prepare the Result object to be returned.
        //? a) Return a successful operation
        if ($rows_affected) {
            return Result::success(
                "The new beer styles were updated successfully!",
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

    public function doDeleteBeerStyles(array $ids): Result
    {

        //TODO: 1) USE THE Valitron library to validate the fields of the new collection to be processed (created, updated, or deleted).

        $errors = [];
        $validation_result = $this->validateInput(
            ['beerStyle_id' => $ids[0] ?? null],
            ['beerStyle_id' => ['required', 'integer']]
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
            $rowsDeleted += $this->beerStyles_model->deleteBeerStyle(['beerStyle_id' => $id]);
        }

        //* 3) Prepare the Result object to be returned.
        //? a) Return a successful operation
        if ($rowsDeleted > 0) {
            return Result::success(
                "The beer styles were deleted successfully!",
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
