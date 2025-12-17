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
        Schema::create('monitoring', function (Blueprint $table) {
            $table->id('id_monitoring');
            $table->unsignedBigInteger('id_laporan');
            $table->unsignedBigInteger('id_operator');
            $table->dateTime('waktu_monitoring');
            $table->text('hasil_monitoring');
            $table->string('koordinat_gps')->nullable();
            $table->timestamps();

            $table->foreign('id_laporan')->references('id_laporan')->on('laporan')->onDelete('cascade');
            $table->foreign('id_operator')->references('id')->on('pengguna')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring');
    }
};
