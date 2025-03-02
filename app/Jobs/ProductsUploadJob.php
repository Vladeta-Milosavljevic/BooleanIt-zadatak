<?php

namespace App\Jobs;

use App\Models\Category;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use stdClass;
use Illuminate\Support\Facades\DB;

class ProductsUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;


    public array $chunkData;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->chunkData = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $validatedData = $this->validateTheBatch();
        // Store the validated inputs in the database
        DB::table('products')->insert($validatedData);
    }


    public function validateTheBatch()
    {
        $records = $this->chunkData;
        $validatedInputs = [];
        $categories = DB::table('categories')->get(['id', 'name']);
        foreach ($records as $data) {
            $record = [
                'product_number' => $data[0],
                'category_name' => $data[1],
                'deparment_name' => $data[2],
                'manufacturer_name' => $data[3],
                'upc' => $data[4],
                'sku' => $data[5],
                'regular_price' => $data[6],
                'sale_price' => $data[7],
                'description' => $data[8],
            ];

            // Define validation rules
            $validator = Validator::make($record, [
                'product_number' => 'required',
                'category_name' => 'required',
                'deparment_name' => 'required',
                'manufacturer_name' => 'required',
                'upc' => 'required',
                'sku' => 'required',
                'regular_price' => 'required',
                'sale_price' => 'required',
                'description' => 'required',
            ]);
            // invalid records are discarted
            if ($validator->fails()) {
                continue;
            };

            // taking data and checking if the product category is in the database
            $categoryInDatabase = $categories->filter(function ($item) use ($record) {
                return $item->name == $record['category_name'];
            });
            // if there is no category a new one is added
            if ($categoryInDatabase->isEmpty()) {
                // adding the new category in the database
                $newCategory = Category::create(['name' => $record['category_name']]);
                $newCategoryObject = new stdClass();
                $newCategoryObject->id = $newCategory['id'];
                $newCategoryObject->name = $newCategory['name'];
                // adding new category to the current list
                $categories->push($newCategoryObject);
                $record['category_id'] = $newCategory->id;
            } else {
                // taking the category id
                $record['category_id'] = $categoryInDatabase->first()->id;
            }
            // removing unnecessary field
            unset($record['category_name']);

            $validatedInputs[] = $record;
        }
        return $validatedInputs;
    }
}
