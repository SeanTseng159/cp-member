<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2018/2/2
 * Time: 下午 01:24
 */

namespace App\Console\Commands\Payment\Tspg;

use App\Plugins\FtpClient;
use App\Services\JWTTokenService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Magento\Payment as MagentoPayment;
use Ksd\Mediation\CityPass\Payment as CityPassPayment;

class AtmOrderCheck extends Command
{
    private $jwtTokenService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:tspg:atm_order_check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tspg atm order check';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(JWTTokenService $jwtTokenService)
    {
        parent::__construct();
        $this->jwtTokenService = $jwtTokenService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $magentoPayment = new MagentoPayment();
        $magentoPayment->tspgATMOrderStatusProcess();

    }

}