<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

use Ksd\Mediation\Magento\Order as MagentoOrder;

class UpdateMagentoCreditCardOrder extends Command
{
    private $magentoOrder;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:update_magento_cc_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update magento credit card orders';

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
        Log::info('====== Update_magento_cc_order Start ======');

        $orders = $this->magentoOrder->pendingOrders();
        $now = Carbon::now();

        if ($orders) {
            foreach ($orders as $key => $order) {
                $orderDate = Carbon::parse($order->orderDate);
                $diffMin = $orderDate->diffInMinutes($now);

                if ($diffMin > 20) {
                    if (count($order->payment) === 0) {
                        $orderDate = Carbon::parse($order->orderDate);
                        $diffMin = $orderDate->diffInMinutes($now);
                        $this->updateOrderState($order->id, $order->orderNo);
                    }
                    elseif (isset($order->payment['method']) && $order->payment['method'] === 'credit_card') {
                        $this->updateOrderState($order->id, $order->orderNo);
                    }
                }
            }
        }

        Log::info('====== Update_magento_cc_order End ======');
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
