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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('pelanggan_id');
            $table->foreign('pelanggan_id')->references('id')->on('pelanggans')->onDelete('cascade');
            $table->uuid('driver_id');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->string('invoice_number');
            $table->bigInteger('total_pay');
            $table->integer('status_pembayaran')->comment('0 = Belum Bayar','1 = Sudah Bayar','3 = Kadaluarsa')->nullable();
            $table->integer('metode_pembayaran')->comment('0 = cod','1 = midtrans')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('subtotal');
            $table->integer('status_order')->comment('0 = diproses','1 = dikirim','3 = selesai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order');
    }
};
