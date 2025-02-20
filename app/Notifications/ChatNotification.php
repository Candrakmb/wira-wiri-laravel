<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ChatNotification extends Notification
{
    use Queueable;

    protected $message;

    // Constructor to accept the message
    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable)
    {
        $chat = $this->message;
        $url = env('URL_FRONT_END').'/';

        return (new WebPushMessage())
            ->title('Pesan dari '.$chat->sender->name)
            ->icon('/notifikasi-icon.jpg')
            ->body($chat->content)
            ->data([
                'url' => $url
            ]);
    }
}
