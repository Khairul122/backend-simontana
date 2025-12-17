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
        Schema::create('riwayat_tindakan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tindaklanjut_id');
            $table->unsignedBigInteger('id_petugas');
            $table->text('keterangan');
            $table->dateTime('waktu_tindakan');
            $table->timestamps();

            $table->foreign('tindaklanjut_id')->references('id_tindaklanjut')->on('tindaklanjut')->onDelete('cascade');
            $table->foreign('id_petugas')->references('id')->on('pengguna')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_tindakan');
    }
};
