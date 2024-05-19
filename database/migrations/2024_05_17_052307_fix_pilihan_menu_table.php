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
        Schema::table('menu_details', function (Blueprint $table) {
            // Menghapus constraint foreign key terlebih dahulu, jika ada
            $table->dropForeign(['menu_id']);
            // Kemudian menghapus indeks jika ada
            $table->dropIndex(['menu_id']);
            // Akhirnya, menghapus kolom
            $table->dropColumn('menu_id');
        });

        // Memodifikasi tabel 'kategori_pilih_menus'
        Schema::table('kategori_pilih_menus', function (Blueprint $table) {
            // Menambahkan kolom 'menu_id' sebagai foreign key dengan cascade delete
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_pilih_menus', function (Blueprint $table) {
            // Menghapus constraint foreign key
            $table->dropForeign(['menu_id']);
            // Menghapus kolom
            $table->dropColumn('menu_id');
        });

        // Membatalkan perubahan di tabel 'menu_details'
        Schema::table('menu_details', function (Blueprint $table) {
            // Menambahkan kembali kolom 'menu_id'
            $table->unsignedBigInteger('menu_id');
            // Menambahkan kembali constraint foreign key
            $table->foreign('menu_id')->references('id')->on('menus');
            // Opsional, tambahkan kembali indeks jika diperlukan
            $table->index('menu_id');
        });
    }
};
