<?php

namespace App\Console\Commands;

use App\Jobs\ProductsUploadJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use App\Models\Category;
use App\Models\Product;
use Generator;
use stdClass;
use Illuminate\Support\Facades\Bus;

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

        $this->info('Importing CSV file...');
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error("The file does not exist.");
            exit;
        }
        // setting up the jobs to upload the data to database
        $batch = Bus::batch([])->dispatch();
        $this->info('Upload start');
        foreach ($this->chunkGenerator($file) as $data) {
            $batch->add(new ProductsUploadJob($data));
        }
        $this->info('Upload finish');
    }



    public function chunkGenerator($file): Generator
    {
        $chunkData = [];
        $chunkSize = 0;
        $dataFile = fopen($file, 'r');
        if ($dataFile !== false) {
            // this skips the header of the file
            fgetcsv($dataFile);

            while (($row = fgetcsv($dataFile)) !== false) {
                $chunkData[] = $row;
                $chunkSize++;
                if ($chunkSize >= 2000) {
                    yield $chunkData;
                    $chunkData = [];
                    $chunkSize = 0;
                }
            }

            if (!empty($chunkData)) {
                yield $chunkData;
                $chunkData = [];

            }
            fclose($dataFile);
        }
    }
}
