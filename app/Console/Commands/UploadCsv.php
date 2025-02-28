<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use App\Models\Category;
use App\Models\Product;
use stdClass;

use function PHPUnit\Framework\isNull;

class UploadCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:upload {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload the CSV data to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {


        $i = 1;
        $chunks = 1000;
        $dataChunk = array();
        $this->info('Importing CSV file...');
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error("The file does not exist.");
            exit;
        }
        $csv = Reader::createFromPath($file, 'r');
        // Skip the header row
        $csv->setHeaderOffset(0);
        // Get the data
        $records = $csv->getRecords();
        // taking the categories from the database
        $categories = DB::table('categories')->get(['id', 'name']);


        $this->info("Validating the data");
        foreach ($records as $record) {
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

            if ($validator->fails()) {
                // sending info to the user, optional because for large CSV files there may be info overload because of potentially many errors
                // $this->info('Validation failed for record: ' . json_encode($record));
                // $this->comment($validator->errors());

                // decreasing the i count so it doesn't include an invalid record
                $i--;
                continue;
            };
            // taking validated data and checking if the product category is in the database
            $validatedRecord = $validator->validated();
            $categoryInDatabase = $categories->filter(function ($item) use ($validatedRecord) {
                return $item->name == $validatedRecord['category_name'];
            });

            if ($categoryInDatabase->isEmpty()) {
                // adding the new category in the database
                $newCategory = Category::create(['name' => $validatedRecord['category_name']]);
                $newCategoryObject = new stdClass();
                $newCategoryObject->id = $newCategory['id'];
                $newCategoryObject->name = $newCategory['name'];
                // adding new category to the current list
                $categories->push($newCategoryObject);
                $validatedRecord['category_id'] = $newCategory->id;
            } else {
                // taking the category id
                $validatedRecord['category_id'] = $categoryInDatabase->first()->id;
            }
            // removing unnecessary field
            unset($validatedRecord['category_name']);

            // updating the database in chunks to save time
            array_push($dataChunk, $validatedRecord);
            if ($i % $chunks == 0) {
                DB::table('products')->insert($dataChunk);
                $dataChunk = array();
            }
            // informing the user about the progress
            if (substr($i, -4) === '0000') {
                $this->info("{$i} records have been uploaded to the database");
            }
            $i++;
        }
        // updating the database with leftover records which are less than chunk size
        if (count($dataChunk) != 0) {
            DB::table('products')->insert($dataChunk);
            $dataChunk = array();
            $this->info("{$i} records have been uploaded");
        }

        $this->info("Number of records is {$i}");
        $this->info("File upload complete");
    }
}
