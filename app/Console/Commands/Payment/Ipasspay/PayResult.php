<?php

namespace App\Console\Commands\Payment\Ipasspay;

use Illuminate\Console\Command;
use Ksd\IPassPay\Services\PayService;
use Ksd\IPassPay\Services\IpasspayLogService;
use Ksd\IPassPay\Parameter\PayParameter;
use Carbon\Carbon;
use Log;

class PayResult extends Command
{
    private $service;
    private $logService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:ipasspay_payresult';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update pay result';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PayService $service, IpasspayLogService $logService)
    {
        parent::__construct();
        $this->service = $service;
        $this->logService = $logService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*$where = [
            'pay_type' => 'VACC',
            'pay_status' => 0
        ];
        $orders = $this->logService->queryOnlyOrderId($where, Carbon::yesterday());

        $payParameter = new PayParameter;

        foreach ($orders as $order) {
            $parameter = $payParameter->bindPayResult($order->order_id);
            $result = $this->service->bindPayResult($parameter);

            # todo
            # 更新兩邊訂單
        }*/
    }
}
