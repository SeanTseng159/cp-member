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
        $schedule->job(new SendNotification())->everyMinute()->withoutOverlapping();

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
