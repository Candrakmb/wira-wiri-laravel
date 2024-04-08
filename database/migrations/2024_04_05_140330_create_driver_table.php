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
        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('no_whatsapp');
            $table->date('tanggal_lahir');
            $table->string('jenis_kelamin');
            $table->string('alamat');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->integer('status',0)->comment('0 = OFF','1 = ON');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver');
    }
};
