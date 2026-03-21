<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->index('username', 'pengguna_username_idx');
            $table->index('email', 'pengguna_email_idx');
        });
    }

    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropIndex('pengguna_username_idx');
            $table->dropIndex('pengguna_email_idx');
        });
    }
};
