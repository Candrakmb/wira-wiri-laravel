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
        // Schema::table('order_detail_ekstras', function (Blueprint $table) {
        //     $table->dropColumn('nama_ekstra');
        // });

        Schema::table('order_destinations', function (Blueprint $table) {
            $table->dropColumn('nama');
            $table->uuid('kedai_id')->nullable();
            $table->unsignedBigInteger('alamat_pelanggan_id')->nullable();
            $table->foreign('alamat_pelanggan_id')
                  ->references('id')
                  ->on('alamat_pelanggans')
                  ->onDelete('cascade');
            $table->foreign('kedai_id')
                  ->nullable()
                  ->references('id')
                  ->on('kedais') // Corrected table name
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_detail_ekstras', function (Blueprint $table) {
            $table->dropColumn('nama_ekstra');
        });
        Schema::dropIfExists('order_destinations');
    }
};
