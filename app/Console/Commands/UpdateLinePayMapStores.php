<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\LinepayStore;

class UpdateLinePayMapStores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:uploadLinePayMapStores {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upload linpay map store from excel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $inputFileName = storage_path('app/LINEPayMap/' . $this->argument('filename'));
        $reader = IOFactory::createReader(IOFactory::identify($inputFileName));
        $spreadsheet = $reader->load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        foreach ($sheetData as $key => $val) {
            if ($key <= 1 ) continue;
            $insert_data['name'] = $val['A'];
            $insert_data['type'] = $val['B'];
            $insert_data['phone'] = $val['C'];
            $insert_data['business_hour'] = $val['D'];
            $insert_data['address'] = $val['E'];
            $insert_data['latitude'] = (float)$val['F'];
            $insert_data['longitude'] = (float)$val['G'];
            LinepayStore::create($insert_data);
        }
    }
}
