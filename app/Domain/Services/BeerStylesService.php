<?php

namespace App\Domain\Services;

use App\Domain\Models\BeerStylesModel;
use App\Helpers\Core\Result;

class BeerStylesService extends BaseService
{
    public function __construct(private BeerStylesModel $beer_styles_model) {}

    //* Implement at least 3 methods for creating, updating, deleting collection items.
    public function doCreateBeerStyle(array $new_beer_styles): Result
    {
        //TODO: 1) USE THE Valitron library to validate the fields of the new collection to be processed (created, updated, or deleted).

        //* 2) Pass the collection item to the model.
        foreach ($new_beer_styles as $key => $new_beer_style) {
            $last_inserted_id = $this->beer_styles_model->insertBeerStyle($new_beer_styles[$key]);
        }


        //* 3) Prepare the Result object to be returned.
        //? a) Return a successful operation
        return Result::success(
            "The new beer styles were created successfully!",
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
