<?php

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
use Psy\ExecutionLoop\Loop;

class AtmSalesAccount extends Command
{
    const CITY_PASS_BUSINESS_CODE = '96681';
    const MAGENTO_BUSINESS_CODE = '96682';

    private $jwtTokenService;

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
        $directory = env('TSPG_ATM_SALES_ACCOUNT_DIR', storage_path('app/tspg'));
        $files = File::files($directory);
        $magentoPayment = new MagentoPayment();
        $cityPassPayment = new CityPassPayment();

        $today = Carbon::today();

        foreach ($files as $file) {
            $filename = $file->getBasename();
            $fileTime = $this->fileTime($filename);

            // 不符合格式，不處理
            if (!$fileTime) continue;

            // 處理過期的檔案
            if (!$fileTime->isToday() && !$fileTime->isYesterday()) {
                $this->ftpMoveExpiredFiles($filename);
                continue;
            }

            if(Cache::has($this->cacheKey($filename))) {
                Log::debug("$filename is run");
                continue;
            }

            // copy到payment getaway
            if (env('APP_ENV') === 'production') {
                $this->moveFilesToPayment($directory, $filename);
            }

            if (!empty($fileTime)) {
                $isSuccess = true;
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
                $cityPassResult = $cityPassPayment->authorization($this->jwtTokenService->generateCityPassBackendToken())
                    ->setJson(true)
                    ->tspgATMReturn($result[ProjectConfig::CITY_PASS]);
                if(!$cityPassResult) {
                    $isSuccess = false;
                    Log::error('city pass fail file:' . $filename);
                }
                fclose($fileResource);
                $this->ftpMoveFiles($filename, $isSuccess);
            }

            Cache::forever($this->cacheKey($filename), true);
        }
    }

    public function fileTime($filename)
    {
        $prefix = 'TSAC53890045';
        $pos = strpos($filename, $prefix);
        if($pos !== false && strpos($filename, $prefix) == 0) {
            $date = Carbon::parse(mb_substr($filename, 12));
            if (!($date->format('H:i:s') == '00:00:00')) {
                return $date;
            }
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

    /**
     * 字串擷取功能
     * @param $line
     * @param $start
     * @param $length
     * @param bool $isTrim
     * @return string
     */
    private function mdSubstr($line, $start, $length, $isTrim = true)
    {
        $str = mb_substr($line, $start, $length);
        if ($isTrim) {
            return trim($str);
        }
        return $str;
    }

    /**
     * 建立快取 key
     * @param $filename
     * @return string
     */
    private function cacheKey($filename)
    {
        return sprintf('tspg:atm_sales_account:%s',$filename);
    }

    /**
     * 搬移銷帳檔案
     * @param $file
     * @param bool $isSuccess
     */
    private function ftpMoveFiles($file, $isSuccess = true)
    {
        $host = env('TSPG_ATM_FTP_HOST');
        $username = env('TSPG_ATM_FTP_USERNAME');
        $password = env('TSPG_ATM_FTP_PASSWORD');

        $client = new FtpClient($host, $username, $password);
        $client->setIsSsl(false);
        if ($isSuccess) {
            $successDir = 'success';
            $client->mkDir($successDir);
            $successPath = sprintf('%s/%s', $successDir, $file);
            $client->moveFile($file, $successPath);

        } else {
            $failDir = 'fail';
            $client->mkDir($failDir);
            $failPath = sprintf('%s/%s', $failDir, $file);
            $client->moveFile($file, $failPath);
        }
    }

    /**
     * 搬移過期銷帳檔案
     * @param $file
     * @param bool $isSuccess
     */
    private function ftpMoveExpiredFiles($file)
    {
        $host = env('TSPG_ATM_FTP_HOST');
        $username = env('TSPG_ATM_FTP_USERNAME');
        $password = env('TSPG_ATM_FTP_PASSWORD');

        $client = new FtpClient($host, $username, $password);
        $client->setIsSsl(false);
        $dir = 'expired';

        $client->mkDir($dir);
        $filePath = sprintf('%s/%s', $dir, $file);
        $client->moveFile($file, $filePath);
    }

    /**
     * 搬移銷帳檔案到payment
     * [之後會撤掉middleare ftp]
     * @param $file
     * @param bool $isSuccess
     */
    private function moveFilesToPayment($directory, $file)
    {
        $host = env('PAYMENT_TSPG_ATM_FTP_HOST');
        $username = env('PAYMENT_TSPG_ATM_FTP_USERNAME');
        $password = env('PAYMENT_TSPG_ATM_FTP_PASSWORD');

        $client = new FtpClient($host, $username, $password);
        $client->setIsSsl(false);

        $tempPath = sprintf('%s/%s', $directory, $file);
        $filePath = sprintf('./%s', $file);

        $client->putFile($tempPath, $filePath);
    }
}
