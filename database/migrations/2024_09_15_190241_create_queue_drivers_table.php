<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('queue_drivers', function (Blueprint $table) {
            $table->id();
            $table->string('driver_id');
            $table->string('order_id');
            $table->dateTime('end_queue');
            $table->dateTime('delete_queue');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_drivers');
    }
};
