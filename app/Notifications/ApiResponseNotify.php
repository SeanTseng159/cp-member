<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ApiResponseNotify extends Notification
{
    use Queueable;
    private $msg;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($msg)
    {
        $this->msg = $msg;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'method' => $this->msg->method,
            'api' => $this->msg->api,
            'respontseTime' => $this->msg->responseTime
        ];
    }

    /**
     * 獲取通知的 Slack 展示方式
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $url = url('/'.$this->msg->api);
        $msg = $this->msg;
        return (new SlackMessage)
            ->from('System', ':male_mage:')
            ->success()
            ->content('Long Response Time API')
            ->attachment(function ($attachment) use ($msg,$url) {
                $attachment->title($msg->api,$url)
                    ->content("{$msg->method} {$msg->api}:{$msg->responseTime}ms");
            });

    }
}
