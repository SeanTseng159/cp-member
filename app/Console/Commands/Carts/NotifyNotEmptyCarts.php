<?php

namespace App\Console\Commands\Carts;

use Illuminate\Console\Command;
use Ksd\Mediation\Magento\Cart as MagentoCart;
use Ksd\Mediation\CityPass\Cart as CityPassCart;
use Log;

class NotifyNotEmptyCarts extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:notify_not_empty_carts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'notify customers who have not empty cart';

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
        
        // 取得符合寄信提醒條件的購物車
        
        // 取得目標會員購物車中所有商品id
        
        // 通知消費者尚有商品未結帳並紀錄此次寄信時間點
        
    }
}
