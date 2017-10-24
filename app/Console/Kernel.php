<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\Notification;
use Ksd\Mediation\Services\NotificationService;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
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
        $schedule->job(function(){

            $now = date("Y-m-d H:i:00");
            $one_minute = date("Y-m-d H:i:00", strtotime("-1 minute"));

            $notification = new Notification();

            $messages = $notification->where('sent', '=', 0)
                         ->where('status', '=', 1)
                         ->where('time', '<=', $now)
                         ->where('time', '>', $one_minute)
                         ->get();

            foreach($messages as $key=>$message){
                $data = array();
                $data['title'] = $messages->title;
                $data['body'] = $messages->body;
                $data['type'] = $messages->type;
                $data['url'] = $messages->url;
                $data['platform'] = $messages->title;

                $notiServ = new NotificationService();

                $notiServ->send($data);

                $message->sent = 1;

                $message->save();
            }



        })->everyMinute();

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
