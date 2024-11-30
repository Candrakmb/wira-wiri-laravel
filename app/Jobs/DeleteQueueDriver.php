<?php

namespace App\Jobs;

use App\Models\Driver;
use App\Models\QueueDriver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DeleteQueueDriver implements ShouldQueue
{
    use Queueable;
    public $driver_id;
    /**
     * Create a new job instance.
     */
    public function __construct($driver_id)
    {
        $this->driver_id = $driver_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $queue = QueueDriver::where('driver_id', $this->driver_id)->first();
        if ($queue) {
            $queue->delete();
        } else {
            Log::info('tidak ditemukan');
        }
    }
}
