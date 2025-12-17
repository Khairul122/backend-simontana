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
        Schema::create('log_activity', function (Blueprint $table) {
            $table->id('id_log');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['Warga', 'OperatorDesa', 'PetugasBPBD', 'Admin']);
            $table->text('aktivitas');
            $table->string('endpoint', 255);
            $table->string('ip_address', 50);
            $table->text('device_info');
            $table->dateTime('created_at');

            $table->foreign('user_id')->references('id')->on('pengguna')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_activity');
    }
};
