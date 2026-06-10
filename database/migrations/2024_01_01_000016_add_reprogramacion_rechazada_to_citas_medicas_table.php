<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas_medicas', function (Blueprint $table) {
            $table->timestamp('reprogramacion_rechazada')->nullable()->after('fecha_reprogramada');
        });
    }

    public function down(): void
    {
        Schema::table('citas_medicas', function (Blueprint $table) {
            $table->dropColumn('reprogramacion_rechazada');
        });
    }
};
