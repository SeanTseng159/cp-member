<?php

namespace App\Console\Commands;

use App\Plugins\FtpClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Ksd\Mediation\Magento\Invoice as MagentoInvoice;

class AutoUploadInvoice extends Command
{
    private $magentoInvoice;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:upload_invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto upload invoice';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MagentoInvoice $magentoInvoice)
    {
        parent::__construct();
        $this->magentoInvoice = $magentoInvoice;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $magentoResult = $this->magentoInvoice->getOrdersBeforeTenDay();
        if (empty($magentoResult) && count($magentoResult) == 0 ) {
            return ;
        }
        /*$businessNo = env('COMPANY_BUSINESS_NO', '53890045');
        $now = Carbon::now();
        $fileName = sprintf('%s-O-M-%s.txt',$businessNo, $now->format('Ymd-His'));
        $tempDir = storage_path('order/invoice');
        $tempPath = sprintf('%s/%s', $tempDir, $fileName);
        if (!file_exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        File::put($tempPath, $magentoResult);

        // 金財通 ftp 設定
        $ftpHost = env('BPSCM_INVOICE_FTP_HOST','61.57.227.80');
        $ftpUser = env('BPSCM_INVOICE_FTP_USERNAME','53890045p');
        $ftpPassword = env('BPSCM_INVOICE_FTP_PASSWORD','b350538$P');
        $ftpClient = new FtpClient($ftpHost,$ftpUser, $ftpPassword);

        $uploadDir = 'Upload';
        $uploadPath = sprintf('%s/%s', $uploadDir, $fileName);
        $ftpClient->putFile($tempPath, $uploadPath);*/

    }
}
