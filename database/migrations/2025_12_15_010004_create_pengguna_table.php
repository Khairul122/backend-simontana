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
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('role', ['Admin', 'PetugasBPBD', 'OperatorDesa', 'Warga'])->default('Warga');
            $table->string('email')->nullable();
            $table->string('no_telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->unsignedBigInteger('id_desa')->nullable();
            $table->timestamps();

            $table->foreign('id_desa')->references('id')->on('desa')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
