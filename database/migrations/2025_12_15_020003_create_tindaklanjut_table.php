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
        Schema::create('tindaklanjut', function (Blueprint $table) {
            $table->id('id_tindaklanjut');
            $table->unsignedBigInteger('laporan_id');
            $table->unsignedBigInteger('id_petugas');
            $table->dateTime('tanggal_tanggapan');
            $table->enum('status', ['Menuju Lokasi', 'Selesai'])->default('Menuju Lokasi');
            $table->timestamps();

            $table->foreign('laporan_id')->references('id')->on('laporans')->onDelete('cascade');
            $table->foreign('id_petugas')->references('id')->on('pengguna')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindaklanjut');
    }
};
