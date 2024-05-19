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
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('no_whatsapp')->change();
        });
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->string('no_whatsapp')->change();
        });
        Schema::table('kedais', function (Blueprint $table) {
            $table->string('no_whatsapp')->change();
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
