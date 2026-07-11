<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosticos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_medica_id')->constrained('consulta_medicas')->cascadeOnDelete();
            $table->string('descripcion');
            $table->string('codigo_cie10')->nullable();
            $table->string('tipo'); // probable, diferencial, definitivo
            $table->boolean('es_principal')->default(false);
            $table->timestamps();
        });

        Schema::table('sintomas', function (Blueprint $table) {
            $table->text('observaciones')->nullable()->after('frecuencia');
        });

        Schema::table('receta_medicamentos', function (Blueprint $table) {
            $table->boolean('incluir_en_receta')->default(true)->after('indicaciones');
            $table->string('concentracion')->nullable()->after('nombre_comercial');
        });
    }

    public function down(): void
    {
        Schema::table('receta_medicamentos', function (Blueprint $table) {
            $table->dropColumn(['incluir_en_receta', 'concentracion']);
        });

        Schema::table('sintomas', function (Blueprint $table) {
            $table->dropColumn('observaciones');
        });

        Schema::dropIfExists('diagnosticos');
    }
};
