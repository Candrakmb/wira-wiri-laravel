<?php

use App\Models\Driver;
use App\Models\Message;
use App\Models\Order;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId); // Lebih efisien menggunakan `find`

    if (!$order) {
        return false; // Jika order tidak ditemukan, tidak ada akses
    }

    $pelanggan = Pelanggan::where('user_id', $user->id)->first();

    if (!$pelanggan) {
        return false; // Jika pelanggan tidak ditemukan, tidak ada akses
    }

    return $pelanggan->id === $order->pelanggan_id;
});

Broadcast::channel('driver.{driverId}', function ($user, $driverId) {
    $order = Order::where('driver_id', $driverId)->where('status_order','>=', 1)->first();

    if (!$order) {
        return false; // Jika order tidak ditemukan, tidak ada akses
    }

    $pelanggan = Pelanggan::where('user_id', $user->id)->first();

    if (!$pelanggan) {
        return false; // Jika pelanggan tidak ditemukan, tidak ada akses
    }

    return $pelanggan->id === $order->pelanggan_id;
});

Broadcast::channel('chat-channel.{id}', function ($user, $id) {
    return $user->id === $id;
});

Broadcast::channel('notif_driver.{id}', function ($user, $id) {
    return $user->id === $id;
});

Broadcast::channel('notif_kedai.{id}', function ($user, $id) {
    return $user->id === $id;
});
