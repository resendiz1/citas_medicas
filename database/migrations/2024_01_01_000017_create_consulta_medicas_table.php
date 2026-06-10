<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_medicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas_medicas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('medico_id')->constrained('users')->cascadeOnDelete();

            $table->text('motivo_consulta')->nullable();
            $table->text('sintomas')->nullable();
            $table->string('tiempo_evolucion')->nullable();

            $table->string('dolor_ubicacion')->nullable();
            $table->string('dolor_intensidad')->nullable();
            $table->string('dolor_duracion')->nullable();

            $table->string('presion_arterial')->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->integer('frecuencia_cardiaca')->nullable();
            $table->integer('frecuencia_respiratoria')->nullable();
            $table->integer('saturacion_oxigeno')->nullable();
            $table->decimal('peso', 5, 2)->nullable();
            $table->decimal('estatura', 5, 2)->nullable();
            $table->decimal('imc', 4, 1)->nullable();

            $table->text('exploracion_fisica')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('diagnostico_probable')->nullable();
            $table->text('diagnostico_final')->nullable();
            $table->string('codigo_cie10')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_medicas');
    }
};
