<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use League\Csv\Writer;
use SplTempFileObject;

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
        $id = $this->argument('id');
        $this->info('Fetching the data');
        $productsPerCategory = Category::with('product')->where('id', $id)->get();
        // checking if there is a category
        if (count($productsPerCategory) === 0) {
            $this->error('Could not find products for this category');
        }
        $products = $productsPerCategory[0]['product'];
        $categoryName = $productsPerCategory[0]['name'];
        $csvHeader = ['product_number', 'category_name', 'deparment_name', 'manufacturer_name', 'upc', 'sku', 'regular_price', 'sale_price', 'description'];
        $csvData = [];
        $this->info('Processing the data');
        // preparing the data
        foreach ($products as $product) {
            $csvData[] = array(
                'product_number' => $product['product_number'],
                'category_name' => $categoryName,
                'deparment_name' => $product['deparment_name'],
                'manufacturer_name' => $product['manufacturer_name'],
                'upc' => $product['upc'],
                'sku' => $product['sku'],
                'regular_price' => $product['regular_price'],
                'sale_price' => $product['sale_price'],
                'description' => $product['description'],
            );
        }
        // create the file name
        $date = now();
        $csvName = preg_replace("/[^A-Za-z0-9 ]/", '_', "{$categoryName}_{$date}");
        $csvName = str_replace(' ', '_', $csvName);
        $this->info("The file name is {$csvName}");

        // create the csv file
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv = Writer::createFromPath("{$csvName}.csv",'w');
        $csv->insertOne($csvHeader);
        $csv->insertAll($csvData);

        exit;

    }
}
