<?php

namespace App\Console\Commands\Payment\Tspg;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Magento\Payment as MagentoPayment;
use Ksd\Mediation\CityPass\Payment as CityPassPayment;

class AtmSalesAccount extends Command
{
    const CITY_PASS_BUSINESS_CODE = '96681';
    const MAGENTO_BUSINESS_CODE = '96682';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:tspg:atm_sales_account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tspg atm sales account';

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
        $directory = env('TSPG_ATM_SALES_ACCOUNT_DIR', storage_path('app/tspg'));
        $files = File::allFiles($directory);
        $magentoPayment = new MagentoPayment();
        $cityPassPayment = new CityPassPayment();

        foreach ($files as $file) {
            $filename = $file->getBasename();
            if(Cache::has($this->cacheKey($filename))) {
                Log::debug("$filename is run");
                continue;
            }

            $fileTime = $this->fileTime($filename);
            if (!empty($fileTime)) {
                $fileResource  = fopen($file, "r");
                if ($fileResource) {
                    $result = [ProjectConfig::MAGENTO => [], ProjectConfig::CITY_PASS => []];
                    while (($line = fgets($fileResource)) !== false) {
                        $row = $this->processData($line);
                        if(strrpos($row->customerVirtualAccount, self::CITY_PASS_BUSINESS_CODE) !== false) {
                            $result[ProjectConfig::CITY_PASS][] = $row;
                        } else if(strrpos($row->customerVirtualAccount, self::MAGENTO_BUSINESS_CODE) !== false) {
                            $result[ProjectConfig::MAGENTO][] = $row;
                        }
                    }
                }
                $magentoPayment->tspgATMReturn($result[ProjectConfig::MAGENTO]);
                if(!$cityPassPayment->tspgATMReturn($result[ProjectConfig::CITY_PASS])) {
                    Log::error('city pass fail file:' . $filename);
                }
                fclose($fileResource);
            }
            Cache::forever($this->cacheKey($filename), true);
        }
    }

    public function fileTime($filename)
    {
        $prefix = 'TSAC53890045';
        if(strpos($filename, $prefix) == 0) {
            return Carbon::parse(mb_substr($filename, 12));
        }
        return null;
    }

    /**
     * 將台新 atm 銷帳資料文字格式轉換為物件
     * @param $line
     * @return \stdClass
     */
    public function processData($line)
    {
        $atm = new \stdClass();
        $atm->code = mb_substr($line, 0 ,4);
        $atm->account = mb_substr($line, 4 , 14);
        $atm->postingTime = Carbon::parse(mb_substr($line, 18 , 8) . ' '. mb_substr($line, 33 , 6)) ;
        $atm->transactionDate = mb_substr($line, 18 , 8);
        $atm->transactionTime = mb_substr($line, 33 , 6);
        $atm->transactionSerial = mb_substr($line, 26 , 6);
        $atm->clearMark = mb_substr($line, 32 , 1);
        $atm->transactionType = $this->mdSubstr($line, 39 , 4);
        $atm->amount = $this->mdSubstr($line, 43 , 12);
        $atm->amountSign = mb_substr($line, 55 , 1);
        $atm->loanType = mb_substr($line, 56 , 1);
        $atm->customerVirtualAccount = $this->mdSubstr($line, 57 , 14);
        $atm->idNumber = $this->mdSubstr($line, 73 , 10);
        $atm->exportBank = mb_substr($line, 83 , 3);
        $atm->memorandum = $this->mdSubstr($line, 86 , 20);
        $atm->status = mb_substr($line, 124 , 1);
        $atm->retention = $this->mdSubstr($line, 106 , 18);
        return $atm;
    }

    private function mdSubstr($line, $start, $length, $isTrim = true)
    {
        $str = mb_substr($line, $start, $length);
        if ($isTrim) {
            return trim($str);
        }
        return $str;
    }

    private function cacheKey($filename)
    {
        return sprintf('tspg:atm_sales_account:%s',$filename);
    }
}
