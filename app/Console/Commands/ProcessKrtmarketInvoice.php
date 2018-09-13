<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Plugins\FtpClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class ProcessKrtmarketInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:process_krtmarket_invoice';
    // protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Krtmarket Invoice';

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
        $today = Carbon::today();

        /**
         * [高捷發票] 上傳至 [金財通FTP]，並將本機資料移至 Upload_OK 資料夾
         */

        // 連線金財通FTP
        $ftpHost = env('BPSCM_INVOICE_FTP_HOST','61.57.227.80');
        $ftpUser = env('BPSCM_INVOICE_FTP_USERNAME','53890045p');
        $ftpPassword = env('BPSCM_INVOICE_FTP_PASSWORD','b350538$P');
        $ftpClient = new FtpClient($ftpHost, $ftpUser, $ftpPassword);

        $dir_upload    = "/home/krtmarket/Upload/";
        $dir_upload_ok = "/home/krtmarket/Upload_OK/";

        $files = File::files($dir_upload);
        foreach ($files as $file) {
            $filename = $file->getBasename();

            // File Upload
            $file_path = $dir_upload.$filename;

            $uploadDir = 'Upload';
            $uploadPath = sprintf('%s/%s', $uploadDir, $filename);
            $ftpClient->putFile($file_path, $uploadPath);
            echo 'upload '.$filename." ok \n";

            // move file to local Upload_OK
            rename($dir_upload.$filename , $dir_upload_ok.$filename);
            echo 'rename '.$filename." OK\n";
        }

    }
}