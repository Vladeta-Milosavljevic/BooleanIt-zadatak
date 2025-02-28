<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use League\Csv\Writer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CscCreateProductFileForSpecificCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:create_product_list_for_category {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching the data');
        $id = $this->argument('id');
        $productsPerCategory = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.product_number',
                'categories.name as category_name',
                'products.deparment_name',
                'products.manufacturer_name',
                'products.upc',
                'products.sku',
                'products.regular_price',
                'products.sale_price',
                'products.description'
            )
            ->where('products.category_id', $id)
            ->get();
        // checking if there is a category
        if (count($productsPerCategory) === 0) {
            $this->error('Could not find products for this category');
            exit;
        }

        // $products = $productsPerCategory[0]['product'];
        $categoryName = $productsPerCategory->first()->category_name;
        $csvHeader = ['product_number', 'category_name', 'deparment_name', 'manufacturer_name', 'upc', 'sku', 'regular_price', 'sale_price', 'description'];
        $csvData = [];
        $this->info('Processing the data');
        // preparing the data array from the laravel colection, toArray() method gives us an array of objects and can't be used as such
        foreach ($productsPerCategory as $product) {
            $csvData[] = array(
                'product_number' => $product->product_number,
                'category_name' => $product->category_name,
                'deparment_name' => $product->deparment_name,
                'manufacturer_name' => $product->manufacturer_name,
                'upc' => $product->upc,
                'sku' => $product->sku,
                'regular_price' => $product->regular_price,
                'sale_price' => $product->sale_price,
                'description' => $product->description,
            );
        }
        // create the file name
        $date = now();
        $csvName = preg_replace("/[^A-Za-z0-9 ]/", '_', "{$categoryName}_{$date}");
        $csvName = str_replace(' ', '_', $csvName);
        $csvName = "{$csvName}.csv";
        $this->info("The file name is {$csvName}");

        // create the csv file
        $this->info("Creating the file");
        $csv = Writer::createFromPath("createdCSVs/{$csvName}", 'w');
        // insert the data
        $this->info("Inserting the data");
        $csv->insertOne($csvHeader);
        $csv->insertAll($csvData);
        $this->info("File is completed");
        $this->info("File is located in the createdCSV folder inside the project and is named {$csvName}");
        exit;
    }
}
