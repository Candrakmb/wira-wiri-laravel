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
        Schema::create('alamat_pelanggans', function (Blueprint $table) {
            $table->id();
            $table->uuid('pelanggan_id');
            $table->foreign('pelanggan_id')->references('id')->on('pelanggans')->onDelete('cascade');
            $table->string('alamat');
            $table->string('tipe_alamat');
            $table->string('detail_alamat');
            $table->string('latitude');
            $table->string('longitude');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamat_pelanggan');
    }
};
