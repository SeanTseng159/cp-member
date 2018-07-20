<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Plugins\FtpClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class DownloadBPSCMFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:download_bpscm_file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download BPSCM File';

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

        /*
         BPSCM: Processing flow
         * 1. 至 [金財通FTP] Download 資料夾，取得檔案列表
         * 2. 判斷後，將[符合檔名]檔案，轉至 DownloadBackup 資料夾
         */

        // 設定本機資料夾(Middleware 測試主機)
        // $dir_upload = "/home/vagrant/code/Download/";
        // $dir_upload_ok = "/home/vagrant/code/Download_OK/";
        $dir_upload    = "/home/krtmarket/Download/";
        $dir_upload_ok = "/home/krtmarket/Download_OK/";

        // 連線至金財通FTP取得目錄檔案列表
        $ftp_server = '61.57.227.80';
        $ftp_user = '53890045p';
        $ftp_pass = 'b350538$P';

        $remote_folder = '/Download/';
        $remote_backup = '/DownloadBackup/';

        // set up a connection or die
        $conn_id = ftp_ssl_connect($ftp_server) or die("Couldn't connect to $ftp_server");

        // try to login
        if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            echo "Connected as $ftp_user@$ftp_server\n";
        } else {
            echo "Couldn't connect as $ftp_user\n";
        }

        // try to change the directory to somedir
        if (ftp_chdir($conn_id, "Download")) {
            echo "Current directory is now: " . ftp_pwd($conn_id) . "\n\n";
        } else {
            echo "Couldn't change directory\n\n";
        }

        ftp_pasv($conn_id, true);
        $filenames = ftp_nlist($conn_id, ".");
        foreach($filenames as $fkey => $filename){
            // echo "$fkey => $filename \n";
            if (strpos($filename, './') === 0) {
                // echo "$fkey => $filename \n";
                $filename = substr($filename, strlen('./'));

                //判斷檔名格式 /^(53890045-InvStatus-)\d{4}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])-\d{6}.txt$/
                if(preg_match("/^(53890045-O-k-)\d{4}(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])(.)+.txt$/", $filename)) {
                    echo 'filename match = '.$filename."\n";

                    // 檢查檔案絕對路徑
                    // echo 'local file: '.$dir_upload.$filename."\n";
                    // echo 'remote file: '.$remote_folder.'/'.$filename."\n";

                    // FTP server file moveTo
                    if (ftp_rename($conn_id, $remote_folder.$filename, $remote_backup.$filename)) {
                        // rename success
                        echo 'rename success'.$filename."\n\n";
                    } else {
                        // rename failed
                        echo 'rename failed'.$filename."\n\n";
                    }
                }
            }
        }
        ftp_close($conn_id);
    }
}
