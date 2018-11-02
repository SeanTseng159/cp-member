<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Console\Commands\AutoUploadInvoice;
use App\Console\Commands\Payment\Tspg\AtmSalesAccount;
use App\Console\Commands\Payment\Tspg\AtmOrderCheck;
use App\Console\Commands\SyncMagentoProduct;
use App\Console\Commands\UpdateMagentoCreditCardOrder;
use App\Console\Commands\UpdateMagentoATMOrder;

use App\Console\Commands\Carts\NotifyNotEmptyCarts;
use App\Console\Commands\Carts\CleanExpiredCarts;
use App\Console\Commands\ProcessKrtmarketInvoice;
use App\Console\Commands\DownloadBPSCMFile;
use App\Console\Commands\ProcessBPSCMFile;
use Ksd\Mediation\Config\ProjectConfig;

use App\Console\Commands\RefreshLayoutCache;
use App\Console\Commands\UpdateLinePayMapStores;

use App\Jobs\SendNotification;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        AtmSalesAccount::class,
        AutoUploadInvoice::class,
        AtmOrderCheck::class,
        SyncMagentoProduct::class,
        UpdateMagentoCreditCardOrder::class,
        UpdateMagentoATMOrder::class,
        CleanExpiredCarts::class,
        NotifyNotEmptyCarts::class,
        ProcessKrtmarketInvoice::class,
        DownloadBPSCMFile::class,
        ProcessBPSCMFile::class,
        RefreshLayoutCache::class,
        UpdateLinePayMapStores::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        // 發送推播訊息
        // $schedule->job(new SendNotification())->everyMinute()->withoutOverlapping();

        // 撈取ftp atm檔案，更新訂單
        $schedule->command(AtmSalesAccount::class)->cron('25 * * * * *');

        // 處理開立發票
        $schedule->command(AutoUploadInvoice::class)->dailyAt('00:30');

        // 移除magento過期ATM訂單
        $schedule->command(AtmOrderCheck::class)->dailyAt('02:00');

        // 更新magento商品
        $schedule->command(SyncMagentoProduct::class)->dailyAt('04:00');

        // 移除magento過期信用卡訂單
        $schedule->command(UpdateMagentoCreditCardOrder::class)->everyTenMinutes();
        // 移除magento過期ATM訂單
        $schedule->command(UpdateMagentoATMOrder::class)->dailyAt('00:00');

        // 清除過期購物車
        $schedule->command(CleanExpiredCarts::class, [ProjectConfig::MAGENTO])->dailyAt('03:00');
        $schedule->command(CleanExpiredCarts::class, [ProjectConfig::CITY_PASS])->dailyAt('04:30');

        //定期提醒購物車中尚有商品
        $schedule->command(NotifyNotEmptyCarts::class)->dailyAt('05:30');

        // 高捷市集發票 upload to 金財通FTP
        // 將排程移至 /etc/crontab 處理
        // $schedule->command(ProcessKrtmarketInvoice::class)->dailyAt('01:00');

        // 金財通FTP轉移資料：Download => DownloadBackup
        $schedule->command(DownloadBPSCMFile::class)->dailyAt('05:00');

        // 金財通FTP(DownloadBackup) download match_file to 本機處理
        // 將排程移至 /etc/crontab 處理
        // $schedule->command(ProcessBPSCMFile::class)->dailyAt('05:02');

        // 重刷快取
        $schedule->command(RefreshLayoutCache::class)->dailyAt('04:30');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
