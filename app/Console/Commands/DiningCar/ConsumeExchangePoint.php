<?php

namespace App\Console\Commands\DiningCar;

use Illuminate\Console\Command;
use App\Services\Ticket\OrderService;
use App\Services\Ticket\DiningCarMemberService;
use App\Services\Ticket\DiningCarPointService;
use App\Jobs\DiningCar\ConsumeAmountExchangePoint;
use App\Core\Logger;

class ConsumeExchangePoint extends Command
{
    protected $service;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:consume_exchange_point';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'consume amount exchange point';

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
    public function handle(OrderService $orderService, DiningCarMemberService $memberService, DiningCarPointService $diningCarPointService)
    {
        $orders = $orderService->getOneHourAgoPaidDiningCarOrders();

        if ($orders->isEmpty()) return;

        Logger::alert('======= Start Exchange Point =======');

        $count = 0;
        foreach ($orders as $order) {
            $member = $memberService->easyFind($order->member_id, $order->dining_car_id);
            if ($member) {
                $key = 'order' . $order->order_id;
                $rule = $diningCarPointService->getExchangeRateRule($order->dining_car_id);
                dispatch(new ConsumeAmountExchangePoint($member, $order->total_amount, $key ,$order->dining_car_id ,$rule))->delay(5);;
                $count++;
            }
        }

        Logger::alert(sprintf('======= End Exchange Point And Count : %s =======', $count));
    }
}
