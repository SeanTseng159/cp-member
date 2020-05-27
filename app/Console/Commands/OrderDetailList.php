<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Core\Logger;

use App\Services\Ticket\OrderDetailService;




class OrderDetailList extends Command
{
    protected $service;

    /**
     * The name and signature of the console command.
     *

     * @var string
     */
    protected $signature = 'run:orderDetailList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'consume orderDetailList';

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


    public function handle(OrderDetailService $service)
    {
        $datas=$service->all();
        
        $array[]=['訂單編號','商品名稱','規格名稱','售價','建立訂單時間','訂單總價'];
        foreach($datas as $data){
            $orderNo=$data->order_no;
            $productName=$data->prod_name;
            $price=$data->price_off;
            $prodSpecName=$data->prod_spec_name;
            $createTime=$data->created_at;
            $oderPrice=$data->order->order_amount;


            $array[]=[$orderNo,$productName,$prodSpecName,$price,$oderPrice];
        }
        $fp = fopen('orderDetail.csv', 'w');
        foreach ($array as $line) {
            fputcsv($fp, $line);
        }
        
        fclose($fp);
    }



}
