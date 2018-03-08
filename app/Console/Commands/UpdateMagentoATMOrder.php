<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

use Ksd\Mediation\Magento\Order as MagentoOrder;

class UpdateMagentoATMOrder extends Command
{
    private $magentoOrder;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:update_magento_atm_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update magento atm orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MagentoOrder $magentoOrder)
    {
        parent::__construct();
        $this->magentoOrder = $magentoOrder;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = $this->magentoOrder->pendingOrders();
        $now = Carbon::now();
        $nowa = Carbon::now()->addDays(1);

        if ($orders) {
            foreach ($orders as $key => $order) {
                $orderDate = Carbon::parse($order->orderDate);
                $orderDate = $orderDate->addDays(1)->toDateString();
                $limitDate = Carbon::parse($orderDate . ' 23:59:59');

                if ($now->gt($limitDate)) {
                    if (isset($order->payment['method']) && $order->payment['method'] === 'atm') {
                        $this->updateOrderState($order->id, $order->orderNo);
                    }
                }
            }
        }
    }

    /**
     * 取得所有訂單列表\
     * @param $email
     * @return array
     */
    private function updateOrderState($id, $incrementId)
    {
        $this->magentoOrder->updateOrderState($id, $incrementId, 'canceled');
    }
}
