<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Console\Commands\AutoUploadInvoice;
use App\Console\Commands\Payment\Tspg\AtmSalesAccount;
use App\Console\Commands\Payment\Tspg\AtmOrderCheck;
use App\Console\Commands\Payment\Ipasspay\PayResult;
use App\Console\Commands\SyncMagentoProduct;
use App\Console\Commands\UpdateMagentoCreditCardOrder;
use App\Console\Commands\UpdateMagentoATMOrder;
use App\Console\Commands\Carts\NotifyNotEmptyCarts;
use App\Console\Commands\Carts\CleanExpiredCarts;
use Ksd\Mediation\Config\ProjectConfig;

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
        PayResult::class,
        AtmOrderCheck::class,
        SyncMagentoProduct::class,
        UpdateMagentoCreditCardOrder::class,
        UpdateMagentoATMOrder::class,
        CleanExpiredCarts::class,
        NotifyNotEmptyCarts::class,
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

        //發送推播訊息
        $schedule->job(new SendNotification())->everyMinute()->withoutOverlapping();
        $schedule->command(AtmSalesAccount::class)->cron('25 * * * * *');
        $schedule->command(AutoUploadInvoice::class)->cron('0 3 * * * *');
        $schedule->command(AtmOrderCheck::class )->dailyAt('02:00');

        // ipaypass 更新ATM狀態
        // $schedule->command(PayResult::class)->cron('10 * * * * *');
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
