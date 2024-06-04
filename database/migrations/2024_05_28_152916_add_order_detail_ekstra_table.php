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
        Schema::table('order_details', function (Blueprint $table) {
            $table->string('price');
        });
        Schema::table('kategori_pilih_menus', function (Blueprint $table) {
            $table->integer('max_pilih')->nullable();
        });

        Schema::create('order_detail_ekstras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_detail_id')->constrained()->onDelete('cascade');
            $table->string('nama_ekstra');
            $table->foreignId('menu_detail_id')->constrained()->onDelete('cascade');
            $table->string('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
