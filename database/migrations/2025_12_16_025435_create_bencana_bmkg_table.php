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
        Schema::create('bencana_bmkg', function (Blueprint $table) {
            $table->id('id_bencana');
            $table->string('jenis_bencana');          // gempa_bumi, cuaca_ekstrem, dll
            $table->string('judul');                  // Judul kejadian
            $table->json('isi_data');                 // JSON data yang diperoleh dari BMKG
            $table->timestamp('waktu_pembaruan');     // Waktu data diperoleh dari BMKG
            $table->string('lokasi')->nullable();     // Lokasi kejadian (opsional)
            $table->decimal('lintang', 10, 8)->nullable(); // Koordinat lintang (opsional)
            $table->decimal('bujur', 11, 8)->nullable();   // Koordinat bujur (opsional)
            $table->decimal('magnitude', 5, 2)->nullable(); // Magnitude jika gempa (opsional)
            $table->string('kedalaman')->nullable();   // Kedalaman jika gempa (opsional)
            $table->string('peringkat')->nullable();  // Tingkat keparahan (opsional)
            $table->string('sumber_data')->nullable(); // URL sumber data BMKG
            $table->timestamps();

            // Indeks untuk performa pencarian
            $table->index(['jenis_bencana', 'waktu_pembaruan']);
            $table->index(['lintang', 'bujur']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bencana_bmkg');
    }
};
