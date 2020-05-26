<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Core\Logger;

use App\Services\Ticket\ProductSpecPriceService;




class ProductAllList extends Command
{
    protected $service;

    /**
     * The name and signature of the console command.
     *

     * @var string
     */
    protected $signature = 'run:productList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'consume productList';

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


    public function handle(ProductSpecPriceService $service)
    {
        $datas=$service->all();

        $array[]=['供應商名稱','商品名稱','規格名稱','售價'];
        foreach($datas as $data){
            $supplier=(empty($data->prodSpec->productAll->supplier->supplier_name))? '' : $data->prodSpec->productAll->supplier->supplier_name;
            $productName=(empty($data->prodSpec->productAll->prod_name))? '' : $data->prodSpec->productAll->prod_name;
            $price=$data->prod_spec_price_value;
            $prodSpecName=$data->prod_spec_price_name;


            $array[]=[$supplier,$productName,$prodSpecName,$price];
        }
        $fp = fopen('file.csv', 'w');
        foreach ($array as $line) {
            fputcsv($fp, $line);
        }
        
        fclose($fp);
    }



}
