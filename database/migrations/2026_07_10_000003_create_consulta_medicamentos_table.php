<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_medicamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_medica_id')->constrained('consulta_medicas')->cascadeOnDelete();
            $table->string('nombre_generico');
            $table->string('nombre_comercial')->nullable();
            $table->string('concentracion')->nullable();
            $table->string('presentacion')->nullable();
            $table->string('forma_farmaceutica')->nullable();
            $table->string('dosis')->nullable();
            $table->string('via_administracion')->nullable();
            $table->string('frecuencia')->nullable();
            $table->string('duracion')->nullable();
            $table->string('cantidad_total')->nullable();
            $table->text('indicaciones')->nullable();
            $table->boolean('incluir_en_receta')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_medicamentos');
    }
};
