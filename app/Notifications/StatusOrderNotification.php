<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class StatusOrderNotification extends Notification
{
    use Queueable;

    protected $order;

    // Constructor to accept the order
    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable)
    {
        $status = $this->order->status_order;
        $url = env('URL_FRONT_END'). $this->order->invoice_number;

        $title = "";
        $body = "";

        switch ($status) {
            case 1:
                $title = "Berhasil Mendapatkan Driver";
                $body = "Yay, driver segera menuju resto";
                break;
            case 2:
                $title = "Driver menuju resto";
                $body = "Driver tancap gas menuju resto";
                break;
            case 3:
                $title = "Driver menuju resto pertama";
                $body = "Driver tancap gas menuju resto pertama";
                break;
            case 4:
                $title = "Driver menuju resto kedua";
                $body = "Driver tancap gas menuju resto kedua";
                break;
            case 5:
                $title = "Driver sampai di resto";
                $body = "Driver sedang memesan pesananmu";
                break;
            case 6:
                $title = "Driver Mengantarkan Pesanan";
                $body = "Driver OTW Mengantarkan pesananmu.";
                break;
            case 7:
                $title = "Pesanan Selesai";
                $body = "Selamat menikmati makanan anda";
                break;
            case 8:
                $title = "Pesanan dibatalkan";
                $body = "Maaf pesanan mu tidak mendapatkan driver";
                break;
            default:
                $title = "Status tidak dikenal";
                $body = "Status order tidak dikenal.";
        }


        return (new WebPushMessage())
            ->title($title)
            ->icon('/notifikasi-icon.jpg')
            ->body($body)
            ->data([
                'url' => $url
            ]);
    }
}
