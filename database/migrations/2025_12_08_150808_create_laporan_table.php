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
            $table->string('pengirim');
            $table->datetime('tanggal_lapor');
            $table->unsignedBigInteger('id_kategori'); // Foreign key ke kategori_bencana
            $table->string('lokasi');
            $table->text('deskripsi');
            $table->string('foto')->nullable();
            $table->string('status_laporan')->default('Dilaporkan');
            $table->foreign('id_warga')->references('id')->on('pengguna')->onDelete('cascade');
            $table->foreign('id_kategori')->references('id_kategori')->on('kategori_bencana')->onDelete('restrict');
            $table->timestamps();
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
