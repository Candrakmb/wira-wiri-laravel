<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_destinations', function (Blueprint $table) {
            $table->uuid('kedai_id')->nullable();
            $table->foreign('kedai_id')->references('id')->on('kedais');
            $table->foreignId('alamat_pelanggan_id')->nullable()->references('id')->on('alamat_pelanggans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('order_destinations', function (Blueprint $table) {
        //     // Menghapus foreign key constraints
        //     $table->dropForeign(['kedai_id']);
        //     $table->dropForeign(['alamat_pelangggan_id']);
            
        //     // Menghapus kolom baru
        //     $table->dropColumn(['kedai_id', 'alamat_pelangggan_id']);
            
        //     // Menambahkan kembali kolom yang dihapus
        //     $table->string('nama');
        //     $table->decimal('latitude', 10, 7);
        //     $table->decimal('longitude', 10, 7);
        // });
    }
};
