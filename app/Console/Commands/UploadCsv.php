<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use App\Models\Category;
use App\Models\Product;

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
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error("The file does not exist.");
            return;
        }

        $csv = Reader::createFromPath($file, 'r');
        // Skip the header row
        $csv->setHeaderOffset(0);
        // Get the data
        $records = $csv->getRecords();

        $categories = Category::get(['id', 'name']);
        $this->info('Importing CSV file...');
        $count = 0;
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
                $this->error('Validation failed for record: ' . json_encode($record));
                $this->comment($validator->errors());
                continue;
            };

            // $categoryInDatabase = array_search($record['category_name'], $categories);
            $categoryInDatabase = $categories->search($record['category_name']);
            if (!$categoryInDatabase) {
                // adding the new category in the database
                $newCategory = Category::create(['name' => $record['category_name']]);
                // adding new category to the current list
                $categories->put($newCategory->id, $newCategory->name);
                $record['category_id'] = $newCategory->id;
            } else {
                $record['category_id'] = $categoryInDatabase;
            }
            // removing unnecessary field
            unset($record['category_name']);
            Product::create($record);
            $count++;
        }

        $this->info("Successfully imported {$count} records.");
    }
}
