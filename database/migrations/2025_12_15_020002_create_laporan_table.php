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
        Schema::create('laporan', function (Blueprint $table) {
            $table->id('id_laporan');
            $table->unsignedBigInteger('id_warga');
            $table->unsignedBigInteger('id_kategori');
            $table->dateTime('tanggal_lapor');
            $table->string('lokasi');
            $table->text('deskripsi');
            $table->string('foto')->nullable();
            $table->enum('status_laporan', ['Dilaporkan', 'Diverifikasi', 'Diterima', 'Selesai'])->default('Dilaporkan');
            $table->enum('prioritas', ['Rendah', 'Sedang', 'Tinggi'])->default('Sedang');
            $table->timestamps();

            $table->foreign('id_warga')->references('id')->on('pengguna')->onDelete('cascade');
            $table->foreign('id_kategori')->references('id')->on('kategori_bencana')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
