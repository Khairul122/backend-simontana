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
            $table->datetime('tanggal_tanggapan');
            $table->string('status'); // 'Menuju Lokasi', 'Selesai', dll
            $table->foreign('laporan_id')->references('id_laporan')->on('laporan')->onDelete('cascade');
            $table->foreign('id_petugas')->references('id')->on('pengguna')->onDelete('cascade');
            $table->timestamps();
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
