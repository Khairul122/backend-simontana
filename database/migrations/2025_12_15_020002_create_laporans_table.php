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

            
            $table->foreignId('id_pelapor')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('id_kategori_bencana')->nullable()->constrained('kategori_bencana')->onDelete('set null');
            $table->foreignId('id_desa')->nullable()->constrained('desa')->onDelete('set null');

            
            $table->string('judul_laporan');
            $table->text('deskripsi');
            $table->enum('tingkat_keparahan', ['Rendah', 'Sedang', 'Tinggi', 'Kritis']);
            $table->enum('status', ['Draft', 'Menunggu Verifikasi', 'Diverifikasi', 'Diproses', 'Selesai', 'Ditolak'])->default('Draft');

            
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('alamat_lengkap')->nullable();

            
            $table->string('foto_bukti_1')->nullable();
            $table->string('foto_bukti_2')->nullable();
            $table->string('foto_bukti_3')->nullable();
            $table->string('video_bukti')->nullable();

            
            $table->foreignId('id_verifikator')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->foreignId('id_penanggung_jawab')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->timestamp('waktu_verifikasi')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->text('catatan_verifikasi')->nullable();

            
            $table->integer('jumlah_korban')->default(0);
            $table->integer('jumlah_rumah_rusak')->default(0);
            $table->json('data_tambahan')->nullable();

            
            $table->boolean('is_prioritas')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamp('waktu_laporan');

            $table->timestamps();

            
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