<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class SendOrderanDriver extends Notification
{
    use Queueable;

    public function via($notifiable)
    {

        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable)
    {
        $url = env('URL_FRONT_END').'driver/orderan';
        Log::info('Notifikasi dikirim ke: ' . $url);
        return (new WebPushMessage())
            ->title('Orderan Masuk')
            ->icon('/notifikasi-icon.jpg')
            ->body('Cek aplikasi segera! Waktu hanya 3 menit.')
            ->data([
                'url' => $url  // URL diperbaiki dengan tanda kutip
            ]);
    }
}
