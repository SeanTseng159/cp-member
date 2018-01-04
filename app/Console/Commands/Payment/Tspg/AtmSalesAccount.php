<?php

namespace App\Console\Commands\Payment\Tspg;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AtmSalesAccount extends Command
{
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
        foreach ($files as $file) {
            $fileResource  = fopen($file, "r");
            if ($fileResource) {
                while (($line = fgets($fileResource)) !== false) {
                    $this->processData($line);
                }
            }
            fclose($fileResource);
        }
    }

    /**
     * 將台新 atm 銷帳資料文字格式轉換為物件
     * @param $line
     */
    public function processData($line)
    {
        $atm = new \stdClass();
        $atm->code = mb_substr($line, 0 ,4);
        $atm->account = mb_substr($line, 4 , 14);
        $atm->postingTime = Carbon::parse(mb_substr($line, 18 , 8) . ' '. mb_substr($line, 33 , 6)) ;
        $atm->transactionSerial = mb_substr($line, 26 , 6);
        $atm->clearMark = mb_substr($line, 32 , 1);
        $atm->transactionType = mb_substr($line, 39 , 4);
        $atm->amount = mb_substr($line, 43 , 12);
        $atm->amountSign = mb_substr($line, 55 , 1);
        $atm->loanType = mb_substr($line, 56 , 1);
        $atm->customerVirtualAccount = mb_substr($line, 57 , 16);
        $atm->idNumber = mb_substr($line, 73 , 10);
        $atm->exportBank = mb_substr($line, 83 , 3);
        $atm->memorandum = mb_substr($line, 86 , 20);
        $atm->status = mb_substr($line, 124 , 1);
    }
}
