<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();

            // Foreign key relations
            $table->foreignId('id_pelapor')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('id_kategori_bencana')->nullable()->constrained('kategori_bencana')->onDelete('set null');
            $table->foreignId('id_desa')->nullable()->constrained('desa')->onDelete('set null');

            // Laporan information
            $table->string('judul_laporan');
            $table->text('deskripsi');
            $table->enum('tingkat_keparahan', ['Rendah', 'Sedang', 'Tinggi', 'Kritis']);
            $table->enum('status', ['Draft', 'Menunggu Verifikasi', 'Diverifikasi', 'Diproses', 'Selesai', 'Ditolak'])->default('Draft');

            // Location data
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('alamat_lengkap')->nullable();

            // Media files
            $table->string('foto_bukti_1')->nullable();
            $table->string('foto_bukti_2')->nullable();
            $table->string('foto_bukti_3')->nullable();
            $table->string('video_bukti')->nullable();

            // Verification and processing
            $table->foreignId('id_verifikator')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->foreignId('id_penanggung_jawab')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->timestamp('waktu_verifikasi')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->text('catatan_verifikasi')->nullable();

            // Additional metadata
            $table->integer('jumlah_korban')->default(0);
            $table->integer('jumlah_rumah_rusak')->default(0);
            $table->json('data_tambahan')->nullable();

            // Flags and counters
            $table->boolean('is_prioritas')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamp('waktu_laporan');

            $table->timestamps();

            // Indexes for performance
            $table->index(['status', 'waktu_laporan']);
            $table->index(['id_kategori_bencana', 'status']);
            $table->index(['id_desa', 'status']);
            $table->index(['is_prioritas', 'status']);
            $table->index(['id_pelapor', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};