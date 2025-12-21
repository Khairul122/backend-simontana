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
        Schema::dropIfExists('bencana_bmkg');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the migration (recreate the table if needed)
        // This would contain the table creation if we want to rollback
    }
};
