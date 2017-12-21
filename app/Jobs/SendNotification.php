<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Notification;
use Ksd\Mediation\Services\NotificationService;


class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
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
            $data['title'] = $message->title;
            $data['body'] = $message->body;
            $data['type'] = $message->type;
            $data['url'] = $message->url;
            $data['platform'] = $message->platform;

            $notiServ = new NotificationService();

            $notiServ->send($data);

            $message->sent = 1;

            $message->save();
        }
    }
}
