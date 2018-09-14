<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Plugins\FtpClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class ProcessBPSCMFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:process_bpscm_file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process BPSCM File';

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
         * 1. 金財通FTP：取得 DownloadBackup 資料夾檔案列表
         * 2. 將[符合檔名]之檔案 下載到本機處理，符合的資料寫至新檔案中
         */

        // 設定本機資料夾(Middleware 測試主機)
        $dir_download    = "/home/krtmarket/Download/";
        $dir_download_ok = "/home/krtmarket/Download_OK/";

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

        // 檢查資料夾檔案名稱符合 /53890045-InvStatus-20180101/ 之當天檔案
        // 留意：檔案太多會 failed
        if (ftp_chdir($conn_id, "DownloadBackup")) {
            echo "Current directory is now: " . ftp_pwd($conn_id) . "\n\n";
        } else {
            echo "Couldn't change directory\n\n";
        }

        /* 為防止 [檔案太多] 造成連線失敗，先檢查本機 Download 是否已成功下載檔案 */
        $localfiles = File::files($dir_download);
        foreach ($localfiles as $localfile) {
            $pattern = '/^(.)+(53890045-InvStatus-)'.date("Ymd").'(.)+.txt$/';
            if (preg_match($pattern, $localfile)) {
                echo 'match = '.$localfile."\n";
                echo "stop process \n";
                exit;
            }
        }

        ftp_pasv($conn_id, true);
        $filenames = ftp_nlist($conn_id, ".");
        foreach($filenames as $fkey => $filename){

            if (strpos($filename, './') === 0) {
                $filename = substr($filename, strlen('./'));

                //判斷檔名格式
                $pattern = '/^(53890045-InvStatus-)'.date("Ymd").'(.)+.txt$/';
                if (preg_match($pattern, $filename)) {
                    echo 'filename match = '.$filename."\n";

                    // DownloadBackup 取出當天檔案做檢查
                    if (ftp_get($conn_id, $dir_download.$filename, $remote_backup.$filename, FTP_ASCII)) {
                         echo "download success: ".$filename."\n\n";
                    } else {
                         echo "download failed: ".$filename."\n\n";
                    }

                    // create new file
                    $data = explode("-", $filename);
                    $newfile = $data[0].'-'.$data[1].'-k-'.$data[2].'-'.$data[3];
                    $newfp = fopen($dir_download.$newfile, 'a');

                    // parse this file
                    $thisfile = fopen($dir_download.$filename, "r");
                    while (!feof($thisfile)) {
                        // echo fgets($thisfile);
                        $str = htmlspecialchars_decode(fgets($thisfile));
                        if (preg_match("/^SO/", $str)) {
                            echo 'match: '.$str."\n";
                            fwrite($newfp, $str);
                        }
                    }
                    fclose($thisfile);
                    fclose($newfp);

                    // 視狀況刪除檔案
                    unlink($dir_download.$filename);
                    if (filesize($dir_download.$newfile) == 0) {
                        unlink($dir_download.$newfile);
                        echo 'delete file:'.$dir_download.$newfile."\n";
                    }

                }
            }

        }
    }
}
