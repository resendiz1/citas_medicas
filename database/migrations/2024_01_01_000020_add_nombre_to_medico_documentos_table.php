<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medico_documentos', function (Blueprint $table) {
            $table->string('nombre')->nullable()->after('medico_perfil_id');
        });
    }

    public function down(): void
    {
        Schema::table('medico_documentos', function (Blueprint $table) {
            $table->dropColumn('nombre');
        });
    }
};
