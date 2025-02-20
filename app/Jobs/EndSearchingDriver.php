<?php

namespace App\Jobs;

use App\Events\StatusOrder;
use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\User;
use App\Notifications\StatusOrderNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EndSearchingDriver implements ShouldQueue
{
    use Queueable;
    public $invoice;
    /**
     * Create a new job instance.
     */
    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::where('invoice_number', $this->invoice)->first();

        if($order && $order->driver_id == null && $order->status_order == 0){
            DB::beginTransaction();
            try {
                $order->status_order = 8;
                $order->save();

                $pelanggan = Pelanggan::where('id', $order->pelanggan_id)->first();
                $user = User::where('id', $pelanggan->user_id)->first();

                broadcast(new StatusOrder($order))->toOthers();
                $user->notify(new StatusOrderNotification($order));
                DB::commit();
            } catch (Exception $e){
                DB::rollback();
                Log::error('Error saat memproses order: ' . $e->getMessage());
            }

        } else {
             Log::info('Order ID: ' . $order->id . ' sudah memiliki driver atau sudah dibatalkan.');
        }
    }
}
