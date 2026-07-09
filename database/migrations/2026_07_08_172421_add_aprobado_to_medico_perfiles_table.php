<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medico_perfiles', function (Blueprint $table) {
            $table->boolean('aprobado')->default(false)->after('intervalo_minutos');
        });

        DB::table('medico_perfiles')->update(['aprobado' => true]);
    }

    public function down(): void
    {
        Schema::table('medico_perfiles', function (Blueprint $table) {
            $table->dropColumn('aprobado');
        });
    }
};
