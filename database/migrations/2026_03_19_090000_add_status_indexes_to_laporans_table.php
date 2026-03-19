<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporans', function (Blueprint $table) {
            $table->index(['created_at', 'status'], 'laporans_created_at_status_idx');
            $table->index(['id_pelapor', 'created_at'], 'laporans_pelapor_created_at_idx');
            $table->index(['id_kategori_bencana', 'created_at'], 'laporans_kategori_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('laporans', function (Blueprint $table) {
            $table->dropIndex('laporans_created_at_status_idx');
            $table->dropIndex('laporans_pelapor_created_at_idx');
            $table->dropIndex('laporans_kategori_created_at_idx');
        });
    }
};
