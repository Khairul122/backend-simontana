<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provinsi', function (Blueprint $table) {
            $table->string('adm1', 2)->nullable()->after('nama');
            $table->unique('adm1', 'provinsi_adm1_unique');
        });

        Schema::table('kabupaten', function (Blueprint $table) {
            $table->string('adm2', 5)->nullable()->after('nama');
            $table->unique('adm2', 'kabupaten_adm2_unique');
        });

        Schema::table('kecamatan', function (Blueprint $table) {
            $table->string('adm3', 8)->nullable()->after('nama');
            $table->unique('adm3', 'kecamatan_adm3_unique');
        });

        Schema::table('desa', function (Blueprint $table) {
            $table->string('adm4', 13)->nullable()->after('nama');
            $table->unique('adm4', 'desa_adm4_unique');
        });
    }

    public function down(): void
    {
        Schema::table('desa', function (Blueprint $table) {
            $table->dropUnique('desa_adm4_unique');
            $table->dropColumn('adm4');
        });

        Schema::table('kecamatan', function (Blueprint $table) {
            $table->dropUnique('kecamatan_adm3_unique');
            $table->dropColumn('adm3');
        });

        Schema::table('kabupaten', function (Blueprint $table) {
            $table->dropUnique('kabupaten_adm2_unique');
            $table->dropColumn('adm2');
        });

        Schema::table('provinsi', function (Blueprint $table) {
            $table->dropUnique('provinsi_adm1_unique');
            $table->dropColumn('adm1');
        });
    }
};
